@php
    $subtitle = $subtitle ?? null;
    $back = $back ?? null;
    $action = $action ?? null;
@endphp

<header class="px-4" style="padding-top: calc(1.25rem + env(safe-area-inset-top));">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if($back)
                <a href="{{ $back }}" class="user-header-icon text-slate-700 bg-white/70 border border-white/60 shadow-sm shrink-0">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-slate-800 leading-tight truncate">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-xs text-slate-500 leading-snug truncate">{{ $subtitle }}</p>
                @endif
            </div>
        </div>

        @if($action)
            <div class="shrink-0">
                {{ $action }}
            </div>
        @endif
    </div>
</header>
