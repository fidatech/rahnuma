<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\{Area, District, Province};
use App\Support\Audit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Provinces, their districts and the areas of a district: the places every office chooses from. */
class LocationController extends Controller
{
    private const MODELS = ['province' => Province::class, 'district' => District::class, 'area' => Area::class];

    public function index(Request $r)
    {
        $province = $r->filled('province') ? Province::find($r->province) : null;
        $district = $r->filled('district') ? District::find($r->district) : null;
        if ($district && ! $province) $province = Province::find($district->province_id);

        if ($district) {
            $level = 'area';
            $rows = Area::where('district_id', $district->id)->orderBy('name_en')->get();
        } elseif ($province) {
            $level = 'district';
            $rows = District::where('province_id', $province->id)->withCount('areas')->orderBy('name_en')->get();
        } else {
            $level = 'province';
            $rows = Province::withCount('districts')->orderBy('name_en')->get();
        }

        return view('platform.locations', compact('level', 'rows', 'province', 'district'));
    }

    public function store(Request $r)
    {
        $d = $this->validated($r);
        $class = self::MODELS[$d['level']];
        $row = $class::create($this->fields($d));
        $this->refresh();
        Audit::log($d['level'].'.created', $row, [], ['name' => $row->name_en]);

        return back()->with('success', __('Place added.'));
    }

    public function update(Request $r, string $level, int $id)
    {
        abort_unless(isset(self::MODELS[$level]), 404);
        $row = (self::MODELS[$level])::findOrFail($id);
        $d = $this->validated($r, $level);
        $old = $row->only(['name_en', 'name_fa', 'name_ps']);
        $row->update(array_intersect_key($this->fields($d), array_flip(['name_en', 'name_fa', 'name_ps', 'code'])));
        $this->refresh();
        Audit::log($level.'.updated', $row, $old, $row->only(['name_en', 'name_fa', 'name_ps']));

        return back()->with('success', __('Place updated.'));
    }

    public function destroy(string $level, int $id)
    {
        abort_unless(isset(self::MODELS[$level]), 404);
        $row = (self::MODELS[$level])::findOrFail($id);
        try {
            Audit::log($level.'.deleted', $row, ['name' => $row->name_en]);
            $row->delete();
        } catch (QueryException) {
            return back()->withErrors(['place' => __('This place is in use and cannot be deleted.')]);
        }
        $this->refresh();

        return back()->with('success', __('Place deleted.'));
    }

    private function validated(Request $r, ?string $level = null): array
    {
        $d = $r->validate([
            'level' => $level ? 'nullable' : 'required|in:province,district,area',
            'name_en' => 'required|max:120', 'name_fa' => 'required|max:120', 'name_ps' => 'required|max:120',
            'code' => 'nullable|max:30', 'province_id' => 'nullable|exists:provinces,id', 'district_id' => 'nullable|exists:districts,id',
        ]);
        $d['level'] = $level ?: $d['level'];
        if ($level === null) {
            if ($d['level'] === 'province' && empty($d['code'])) abort(back()->withErrors(['code' => __('A province needs a short code.')])->withInput());
            if ($d['level'] === 'district' && empty($d['province_id'])) abort(404);
            if ($d['level'] === 'area' && empty($d['district_id'])) abort(404);
        }

        return $d;
    }

    private function fields(array $d): array
    {
        $fields = ['name_en' => $d['name_en'], 'name_fa' => $d['name_fa'], 'name_ps' => $d['name_ps']];
        if ($d['level'] === 'province') $fields['code'] = strtoupper((string) ($d['code'] ?? ''));
        if ($d['level'] === 'district') {
            $fields['province_id'] = $d['province_id'] ?? null;
            if (! empty($d['code'])) $fields['code'] = strtoupper($d['code']);
        }
        if ($d['level'] === 'area') $fields['district_id'] = $d['district_id'] ?? null;

        return array_filter($fields, fn ($v) => $v !== null && $v !== '');
    }

    private function refresh(): void
    {
        Cache::forget('rahnuma:provinces:list:v2');
        Cache::forget('rahnuma:marketplace:home:v9');
    }
}
