@extends('layouts.app')

@section('title', 'E-Presensi')

@section('content')
<style>
    .user-dashboard-main {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        align-items: start;
    }

    .user-dashboard-main > .span-full {
        grid-column: 1 / -1;
    }

    .dashboard-menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(5rem, 1fr));
    }

    .dashboard-menu-card {
        min-height: 5.75rem;
    }

    .notification-panel {
        width: min(19rem, calc(100vw - 3rem));
        max-height: min(23rem, calc(100vh - 8.5rem));
    }

    @media (max-width: 360px) {
        .dashboard-menu-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .notification-panel {
            width: min(17.5rem, calc(100vw - 2rem));
        }
    }

    @media (min-width: 640px) {
        .dashboard-menu-grid {
            grid-template-columns: repeat(auto-fit, minmax(6.5rem, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .user-dashboard-main {
            grid-template-columns: minmax(0, 1fr) minmax(20rem, 26rem);
        }
    }

    @media (min-width: 1180px) {
        .user-dashboard-main {
            grid-template-columns: minmax(36rem, 1fr) minmax(24rem, 30rem);
        }
    }
</style>
@php
    $jadwalMasuk = isset($scheduledShift) && $scheduledShift?->jam_masuk
        ? $scheduledShift->jam_masuk->format('H:i')
        : '--:--';
    $jadwalPulang = isset($scheduledShift) && $scheduledShift?->jam_pulang
        ? $scheduledShift->jam_pulang->format('H:i')
        : '--:--';
    $jamMasuk = $presensiHariIni?->jam_masuk?->format('H:i') ?? null;
    $jamPulang = $presensiHariIni?->jam_keluar?->format('H:i') ?? null;
    $sudahMasuk = (bool) $presensiHariIni?->jam_masuk;
    $sudahPulang = (bool) $presensiHariIni?->jam_keluar;
    $statusHariIni = !$sudahMasuk
        ? 'Belum Absen'
        : (in_array($presensiHariIni?->status, ['telat', 'terlambat'], true) ? 'Telat' : 'Tepat Waktu');
    $statusBadgeClass = !$sudahMasuk
        ? 'bg-slate-100 text-slate-600'
        : (in_array($presensiHariIni?->status, ['telat', 'terlambat'], true) ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700');
    $hasScheduledShift = isset($scheduledShift) && $scheduledShift;
    $isShiftOff = $hasScheduledShift && $scheduledShift->status === 'libur';
    $shiftLabel = $hasScheduledShift ? $scheduledShift->nama_shift : null;
    $announcementCount = $announcements->count();
    $featureSettings = \App\Models\FeatureSetting::matrix();
@endphp

<div class="user-page">
    <div class="user-phone">
        <header class="px-4 pt-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-700 leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 leading-tight">Akun User</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <div class="relative" id="notificationWrap">
                        <button type="button" id="notificationButton" class="relative w-9 h-9 rounded-xl bg-white/70 hover:bg-white text-blue-700 flex items-center justify-center shadow-sm border border-white/60">
                            <i class="fa-solid fa-bell"></i>
                            @if($announcementCount > 0)
                                <span id="notificationBadge" class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center border-2 border-white">
                                    {{ $announcementCount > 9 ? '9+' : $announcementCount }}
                                </span>
                            @endif
                        </button>

                        <div id="notificationPanel" class="notification-panel hidden absolute right-0 top-11 z-50 rounded-2xl bg-white border border-slate-100 shadow-xl overflow-hidden">
                            <div class="px-3 py-2.5 border-b border-slate-100">
                                <p class="text-sm font-bold text-slate-800">Notifikasi</p>
                                <p id="notificationCountText" class="text-[11px] text-slate-500">{{ $announcementCount }} pemberitahuan aktif</p>
                            </div>
                            <div id="notificationList" class="max-h-[18rem] overflow-y-auto">
                                @forelse($announcements as $announcement)
                                    <div class="notification-item px-3 py-2.5 border-b border-slate-100 last:border-b-0 transition-transform duration-150"
                                         data-dismiss-url="{{ route('announcements.dismiss', $announcement, false) }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-xs font-bold text-slate-800 leading-snug">{{ $announcement->judul }}</p>
                                            @if($announcement->target_type === 'users')
                                                <span class="shrink-0 rounded-full bg-blue-50 text-blue-700 px-2 py-0.5 text-[10px] font-bold">Khusus</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-[11px] text-slate-500">{{ $announcement->tanggal_mulai->format('d/m/Y') }} - {{ $announcement->tanggal_berakhir->format('d/m/Y') }}</p>
                                        <p class="mt-2 text-[11px] text-slate-600 leading-relaxed line-clamp-2">{{ $announcement->isi }}</p>
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-slate-500">
                                        Belum ada notifikasi.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="w-9 h-9 rounded-xl bg-white/70 hover:bg-white text-slate-700 flex items-center justify-center shadow-sm border border-white/60">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-3 flex flex-col items-center">
                <div id="bigClock" class="text-4xl font-extrabold tracking-tight text-blue-800 leading-none">--:--:--</div>
                <div class="mt-1 text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
        </header>

        <main class="user-dashboard-main px-4 pt-4 gap-4">
            @if(!$hasScheduledShift)
                <section class="span-full bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-4 text-sm shadow-sm">
                    Shift kamu belum diatur oleh admin untuk hari ini. Absen hanya bisa dilakukan setelah ada jadwal shift.
                </section>
            @elseif($isShiftOff)
                <section class="span-full bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl p-4 text-sm shadow-sm">
                    Hari ini kamu dijadwalkan libur.
                </section>
            @endif

            <section class="span-full bg-white/80 backdrop-blur rounded-2xl shadow-sm border border-white/70 overflow-hidden">
                <div class="grid grid-cols-2 divide-x divide-slate-100">
                    <div class="p-3 flex items-center gap-2 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                            <i class="fa-solid fa-right-to-bracket"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] text-slate-500 leading-tight">Jam Masuk</p>
                            <p class="text-sm font-bold text-slate-800 leading-tight truncate">{{ $jamMasuk ?? $jadwalMasuk }}</p>
                            @if($shiftLabel)
                                <p class="text-[11px] text-slate-500 leading-tight truncate">{{ $shiftLabel }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="p-3 flex items-center gap-2 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] text-slate-500 leading-tight">Jam Pulang</p>
                            <p class="text-sm font-bold text-slate-800 leading-tight truncate">{{ $jamPulang ?? ($shiftLabel ? $jadwalPulang : 'Belum Dijadwalkan') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-menu-grid gap-3">
                @php
                    $menu = [
                        ['label' => 'Hadir', 'icon' => 'fa-user-check', 'badge' => $hadir, 'url' => route('history.index')],
                        ['label' => 'Sakit', 'icon' => 'fa-user-injured', 'badge' => 0, 'url' => route('features.show', 'sakit'), 'feature' => 'sakit'],
                        ['label' => 'Izin', 'icon' => 'fa-clipboard-check', 'badge' => $izin, 'url' => route('leave_requests.index')],
                        ['label' => 'Cuti', 'icon' => 'fa-plane-departure', 'badge' => 0, 'url' => route('features.show', 'cuti'), 'feature' => 'cuti'],
                        ['label' => 'ID Card', 'icon' => 'fa-id-card', 'badge' => 0, 'url' => route('profile.index')],
                        ['label' => 'Lembur', 'icon' => 'fa-clock', 'badge' => 0, 'url' => route('features.show', 'lembur'), 'feature' => 'lembur'],
                        ['label' => 'Jadwal', 'icon' => 'fa-calendar-days', 'badge' => 0, 'url' => route('user.shifts.index')],
                        ['label' => 'Swap Shift', 'icon' => 'fa-right-left', 'badge' => 0, 'url' => route('shift-swaps.index')],
                    ];
                @endphp

                @foreach($menu as $item)
                    @continue(isset($item['feature']) && !($featureSettings[$item['feature']]['user'] ?? false))
                    <a href="{{ $item['url'] ?? '#' }}"
                       class="dashboard-menu-card relative bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm px-2 py-3 text-center active:scale-[0.99] transition">
                        @if(($item['badge'] ?? 0) > 0)
                            <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ (int) $item['badge'] }}
                            </span>
                        @endif
                        <div class="w-10 h-10 mx-auto rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </div>
                        <p class="mt-2 text-[11px] font-semibold text-slate-700 leading-tight">{{ $item['label'] }}</p>
                    </a>
                @endforeach
            </section>

            <section class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">30 Hari terakhir</p>
                        <p class="text-[11px] text-slate-500">Ringkasan absensi</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusBadgeClass }}">{{ $statusHariIni }}</span>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-xl bg-blue-50 p-2">
                        <p class="text-sm font-extrabold text-blue-700">{{ $hadir }}</p>
                        <p class="text-[11px] text-slate-500">Hadir</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-2">
                        <p class="text-sm font-extrabold text-amber-700">{{ $telat }}</p>
                        <p class="text-[11px] text-slate-500">Telat</p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-2">
                        <p class="text-sm font-extrabold text-red-600">{{ $izin }}</p>
                        <p class="text-[11px] text-slate-500">Izin</p>
                    </div>
                </div>
            </section>

            @if($approvedLeaveToday)
                <section class="bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 text-sm shadow-sm">
                    Hari ini Anda memiliki izin yang disetujui: <span class="font-bold">{{ ucfirst($approvedLeaveToday->jenis_izin) }}</span>.
                </section>
            @endif

            <section class="span-full space-y-3">
                @php
                    $shiftLabel = 'SHIFT 1';
                    $shiftJam = $jadwalMasuk . ' - ' . $jadwalPulang;
                @endphp

                @forelse($recentPresensis as $item)
                    @php
                        $itemTanggal = optional($item->tanggal)->translatedFormat('d F Y') ?? '-';
                        $itemMasuk = $item->jam_masuk?->format('H:i');
                        $itemPulang = $item->jam_keluar?->format('H:i');
                        $itemRange = $itemMasuk
                            ? ($itemMasuk . ' - ' . ($itemPulang ?? 'Belum Absen'))
                            : 'Belum Absen';
                        $itemStatus = $itemMasuk
                            ? (in_array($item->status, ['telat', 'terlambat'], true) ? 'Telat' : 'Tepat Waktu')
                            : 'Belum Absen';
                        $itemBadge = !$itemMasuk
                            ? 'bg-slate-100 text-slate-600'
                            : (in_array($item->status, ['telat', 'terlambat'], true) ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700');
                    @endphp

                    <div class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                                    <i class="fa-solid fa-fingerprint"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-800 leading-tight">{{ $itemTanggal }}</p>
                                    <p class="text-[11px] text-slate-500 leading-tight">{{ $itemRange }}</p>
                                    <span class="inline-flex mt-2 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $itemBadge }}">{{ $itemStatus }}</span>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-[11px] font-bold text-slate-700 leading-tight">{{ $shiftLabel }}</p>
                                <p class="text-[11px] text-slate-500 leading-tight">{{ $shiftJam }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-6 text-center text-sm text-slate-500">
                        Belum ada data presensi.
                    </div>
                @endforelse
            </section>
        </main>

        <nav class="user-bottom-nav">
            <div class="user-bottom-nav-inner">
                <a href="{{ route('dashboard') }}" class="text-blue-700 text-center text-xs">
                    <i class="fa-solid fa-house text-lg"></i>
                    <p>Home</p>
                </a>
                <a href="{{ route('history.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-file-lines text-lg"></i>
                    <p>Histori</p>
                </a>
                <a href="{{ route('absen.page') }}" class="w-14 h-14 -mt-8 bg-red-600 text-white rounded-full flex items-center justify-center border-4 border-white shadow-lg shadow-red-600/20">
                    <i class="fa-solid fa-fingerprint text-xl"></i>
                </a>
                <a href="{{ route('user.shifts.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                    <p>Jadwal</p>
                </a>
                <a href="{{ route('profile.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-id-card text-lg"></i>
                    <p>Biodata</p>
                </a>
            </div>
        </nav>
    </div>
</div>

<script>
function updateClock() {
    const now = new Date();
    const clockText = now.toLocaleTimeString('id-ID');
    const clock = document.getElementById('clock');
    if (clock) clock.innerText = clockText;
    const bigClock = document.getElementById('bigClock');
    if (bigClock) bigClock.innerText = clockText;
}
setInterval(updateClock, 1000);
updateClock();

const notificationWrap = document.getElementById('notificationWrap');
const notificationButton = document.getElementById('notificationButton');
const notificationPanel = document.getElementById('notificationPanel');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

if (notificationWrap && notificationButton && notificationPanel) {
    notificationButton.addEventListener('click', (event) => {
        event.stopPropagation();
        notificationPanel.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!notificationWrap.contains(event.target)) {
            notificationPanel.classList.add('hidden');
        }
    });
}

document.querySelectorAll('.notification-item').forEach((item) => {
    let startX = 0;
    let currentX = 0;
    let dragging = false;

    item.addEventListener('touchstart', (event) => {
        startX = event.touches[0].clientX;
        currentX = startX;
        dragging = true;
        item.style.transition = 'none';
    }, { passive: true });

    item.addEventListener('touchmove', (event) => {
        if (!dragging) return;
        currentX = event.touches[0].clientX;
        const diff = currentX - startX;
        item.style.transform = `translateX(${diff}px)`;
        item.style.opacity = String(Math.max(0.35, 1 - Math.abs(diff) / 180));
    }, { passive: true });

    item.addEventListener('touchend', () => {
        if (!dragging) return;
        dragging = false;
        item.style.transition = '';

        const diff = currentX - startX;
        if (Math.abs(diff) < 80) {
            item.style.transform = '';
            item.style.opacity = '';
            return;
        }

        const dismissDirection = diff > 0 ? 120 : -120;
        item.style.transform = `translateX(${dismissDirection}%)`;
        item.style.opacity = '0';

        fetch(item.dataset.dismissUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        }).then((response) => {
            if (!response.ok) throw new Error('Dismiss failed');
            item.remove();
            const countText = document.getElementById('notificationCountText');
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');
            const remaining = document.querySelectorAll('.notification-item').length;
            if (countText) countText.textContent = `${remaining} pemberitahuan aktif`;
            if (badge) {
                if (remaining > 0) {
                    badge.textContent = remaining > 9 ? '9+' : String(remaining);
                } else {
                    badge.remove();
                }
            }
            if (list && remaining === 0) {
                list.innerHTML = '<div class="px-4 py-6 text-center text-sm text-slate-500">Belum ada notifikasi.</div>';
            }
        }).catch(() => {
            item.style.transform = '';
            item.style.opacity = '';
        });
    });
});
</script>
@endsection
