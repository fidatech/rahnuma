{{--
    The bell of the top bar. Alerts that ask for an action count until the action is taken; the
    others count as new until the list has been opened once (remembered in the browser).
    The inner part is fetched again every minute by rahnuma-notify.js.
--}}
@php $alerts = \App\Support\Notifications::for(auth()->user()); @endphp
<div class="notif-inner" data-count-open="{{ collect($alerts)->where('open', true)->count() }}">
    <div class="notif-head"><strong>{{ __('Notifications') }}</strong><span class="small text-muted">{{ __(':count alerts', ['count' => count($alerts)]) }}</span></div>
    <div class="notif-body">
        @forelse ($alerts as $alert)
            <a class="notif-item {{ $alert['open'] ? 'unread' : '' }}" href="{{ $alert['url'] }}" data-native @if (! $alert['open'] && $alert['at']) data-at="{{ $alert['at'] }}" @endif>
                <span class="ni badge-soft {{ $alert['tone'] }}"><x-icon :name="$alert['icon']" /></span>
                <span class="min-w-0"><strong>{{ $alert['title'] }}</strong><small class="text-truncate">{{ $alert['text'] }}</small>@if ($alert['at'])<em dir="ltr">{{ \Illuminate\Support\Carbon::parse($alert['at'])->format('Y-m-d H:i') }}</em>@endif</span>
            </a>
        @empty
            <div class="notif-empty"><x-icon name="circle-check" /><span>{{ __('Nothing needs your attention.') }}</span></div>
        @endforelse
    </div>
</div>
