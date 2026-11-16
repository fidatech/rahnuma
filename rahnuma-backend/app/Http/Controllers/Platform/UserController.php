<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Agency, User};
use App\Support\Audit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public const ROLES = ['platform_admin', 'government_admin', 'inspector', 'agency_owner', 'agency_manager', 'agent', 'accountant', 'cashier', 'data_entry', 'viewer'];
    private const PANEL_ROLES = ['platform_admin', 'government_admin', 'inspector'];

    public function index(Request $r)
    {
        $users = User::with('agency:id,name,slug')
            ->when($r->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$r->q.'%')->orWhere('email', 'like', '%'.$r->q.'%')))
            ->when($r->filled('role'), fn ($q) => $q->where('role', $r->role))
            ->when($r->filled('agency_id'), fn ($q) => $q->where('agency_id', $r->agency_id))
            ->when($r->status === 'active', fn ($q) => $q->where('is_active', 1))
            ->when($r->status === 'disabled', fn ($q) => $q->where('is_active', 0))
            ->orderByRaw("CASE role WHEN 'platform_admin' THEN 0 WHEN 'government_admin' THEN 1 WHEN 'inspector' THEN 2 ELSE 3 END")
            ->orderBy('name')->paginate(30)->withQueryString();

        return view('platform.users', [
            'users' => $users,
            'roles' => self::ROLES,
            'agencies' => Agency::orderBy('name')->get(['id', 'name']),
            'counts' => ['all' => User::count(), 'admins' => User::where('role', 'platform_admin')->count(), 'government' => User::whereIn('role', ['government_admin', 'inspector'])->count(), 'disabled' => User::where('is_active', 0)->count()],
        ]);
    }

    public function store(Request $r)
    {
        $d = $this->validated($r) + $r->validate(['password' => 'required|min:8']);
        $d['password'] = Hash::make($d['password']);
        $d['is_active'] = 1;
        $user = User::create($d);
        Audit::log('user.created', $user, [], ['email' => $user->email, 'role' => $user->role]);

        return back()->with('success', __('User created.'));
    }

    public function update(Request $r, User $user)
    {
        $d = $this->validated($r, $user);
        // the last administrator cannot be turned into something else
        if ($user->role === 'platform_admin' && $d['role'] !== 'platform_admin' && User::where('role', 'platform_admin')->where('is_active', 1)->count() <= 1) {
            return back()->withErrors(['role' => __('The system needs at least one active administrator.')]);
        }
        $old = $user->only(['name', 'email', 'role', 'agency_id', 'locale']);
        $user->update($d);
        Audit::log('user.updated', $user, $old, $user->only(['name', 'email', 'role', 'agency_id', 'locale']));

        return back()->with('success', __('User updated.'));
    }

    public function password(Request $r, User $user)
    {
        $d = $r->validate(['password' => 'required|min:8']);
        $user->forceFill(['password' => Hash::make($d['password'])])->save();
        Audit::log('user.password_reset', $user);

        return back()->with('success', __('Password changed.'));
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['user' => __('You cannot disable your own account.')]);
        if ($user->role === 'platform_admin' && $user->is_active && User::where('role', 'platform_admin')->where('is_active', 1)->count() <= 1) {
            return back()->withErrors(['user' => __('The system needs at least one active administrator.')]);
        }
        $user->update(['is_active' => ! $user->is_active]);
        Audit::log($user->is_active ? 'user.enabled' : 'user.disabled', $user);

        return back()->with('success', $user->is_active ? __('Account enabled.') : __('Account disabled.'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return back()->withErrors(['user' => __('You cannot delete your own account.')]);
        if ($user->role === 'platform_admin' && User::where('role', 'platform_admin')->count() <= 1) {
            return back()->withErrors(['user' => __('The system needs at least one active administrator.')]);
        }

        try {
            Audit::log('user.deleted', $user, ['email' => $user->email, 'role' => $user->role]);
            $user->delete();
        } catch (QueryException) {
            return back()->withErrors(['user' => __('This user has records in the system. Disable the account instead.')]);
        }

        return back()->with('success', __('User deleted.'));
    }

    /** Sign in as another user to see what they see. The way back is kept in the session. */
    public function impersonate(Request $r, User $user)
    {
        abort_if($user->role === 'platform_admin', 403, __('An administrator account cannot be opened this way.'));
        abort_unless($user->is_active, 403, __('Account disabled.'));

        $admin = $r->user();
        Audit::log('user.impersonated', $user, [], ['by' => $admin->email]);
        Auth::login($user);
        $r->session()->regenerate();
        $r->session()->put('impersonator_id', $admin->id);

        return redirect()->to($user->isGovernment() ? route('government.dashboard') : route('tenant.dashboard'));
    }

    /** Back to the administrator account. Open to any signed-in session that holds the way back. */
    public function stopImpersonating(Request $r)
    {
        $admin = User::where('role', 'platform_admin')->where('is_active', 1)->find($r->session()->pull('impersonator_id'));
        abort_unless($admin, 403);

        Auth::login($admin);
        $r->session()->regenerate();
        Audit::log('user.impersonation_ended');

        return redirect()->route('platform.users');
    }

    private function validated(Request $r, ?User $user = null): array
    {
        $d = $r->validate([
            'name' => 'required|max:180',
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'agency_id' => 'nullable|exists:agencies,id',
            'locale' => 'required|in:en,fa,ps',
        ]);

        // an account of the platform or of the authority belongs to no office; an office account needs one
        if (in_array($d['role'], self::PANEL_ROLES, true)) {
            $d['agency_id'] = null;
        } elseif (empty($d['agency_id'])) {
            abort(back()->withErrors(['agency_id' => __('Choose the office this account belongs to.')])->withInput());
        }

        return $d;
    }
}
