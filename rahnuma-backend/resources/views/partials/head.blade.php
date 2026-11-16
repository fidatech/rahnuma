@php
    $pageTitle = trim($__env->yieldContent('title'));
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#0a1f3d">
<title>{{ $pageTitle !== '' ? $pageTitle.' | ' : '' }}{{ config('app.name') }}</title>
<link rel="icon" href="{{ asset_v('img/favicon.svg') }}" type="image/svg+xml">
<script>(function(){var s={};try{s=JSON.parse(localStorage.getItem('erp.ui')||'{}')}catch(e){}var h=document.documentElement;h.setAttribute('data-bs-theme',s.theme==='dark'?'dark':'light');if(h.hasAttribute('data-layout'))h.setAttribute('data-layout',s.layout==='collapsed'?'collapsed':'vertical')})();</script>
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
<link rel="stylesheet" href="{{ asset_v(text_dir() === 'rtl' ? 'css/app.rtl.css' : 'css/app.css') }}">
{{ \App\Support\Fonts::style() }}
