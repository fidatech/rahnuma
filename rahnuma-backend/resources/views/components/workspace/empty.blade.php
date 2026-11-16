@props(['title' => null, 'description' => null, 'icon' => 'folder-open'])
<div class="empty-state">
    <div class="es-icon"><x-icon :name="$icon" /></div>
    <h3>{{ $title ?? __('Nothing here yet') }}</h3>
    <p>{{ $description ?? __('Create the first record to get started.') }}</p>
</div>
