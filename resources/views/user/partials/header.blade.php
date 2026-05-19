@php
    $subtitle = $subtitle ?? null;
    $back = $back ?? null;
    $action = $action ?? null;
@endphp

<header class="px-4 pt-4">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3 min-w-0">
            @if($back)
                <a href="{{ $back }}" class="user-header-icon text-slate-700 bg-white/70 border border-white/60 shadow-sm shrink-0">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-slate-800 leading-tight">{{ $title }}</h1>
                @if($subtitle)
                    <p class="text-xs text-slate-500 leading-snug">{{ $subtitle }}</p>
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
