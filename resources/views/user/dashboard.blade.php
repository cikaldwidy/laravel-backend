@extends('layouts.app')

@section('title', 'E-Presensi')

@section('content')
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
        : (in_array($presensiHariIni?->status, ['telat', 'terlambat'], true) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
    $hasScheduledShift = isset($scheduledShift) && $scheduledShift;
    $isShiftOff = $hasScheduledShift && $scheduledShift->status === 'libur';
    $shiftLabel = $hasScheduledShift ? $scheduledShift->nama_shift : null;
    $featureSettings = \App\Models\FeatureSetting::matrix();
@endphp

<div class="min-h-[100dvh] bg-cyan-50 flex justify-center">
    <div class="w-full max-w-sm min-h-[100dvh] bg-[#dffcff] shadow-2xl relative pb-[calc(6rem+env(safe-area-inset-bottom))]">
        <header class="px-4 pt-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-700 leading-tight">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 leading-tight">Akun User</p>
                </div>

                <form method="POST" action="/logout" class="shrink-0">
                    @csrf
                    <button type="submit" class="w-9 h-9 rounded-xl bg-white/70 hover:bg-white text-slate-700 flex items-center justify-center shadow-sm border border-white/60">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </button>
                </form>
            </div>

            <div class="mt-3 flex flex-col items-center">
                <div id="bigClock" class="text-4xl font-extrabold tracking-tight text-emerald-800 leading-none">--:--:--</div>
                <div class="mt-1 text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</div>
            </div>
        </header>

        <main class="px-4 pt-4 space-y-4">
            @if(!$hasScheduledShift)
                <section class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-4 text-sm shadow-sm">
                    Shift kamu belum diatur oleh admin untuk hari ini. Absen hanya bisa dilakukan setelah ada jadwal shift.
                </section>
            @elseif($isShiftOff)
                <section class="bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl p-4 text-sm shadow-sm">
                    Hari ini kamu dijadwalkan libur.
                </section>
            @endif

            <section class="bg-white/80 backdrop-blur rounded-2xl shadow-sm border border-white/70 overflow-hidden">
                <div class="grid grid-cols-2 divide-x divide-slate-100">
                    <div class="p-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <i class="fa-solid fa-right-to-bracket"></i>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-500 leading-tight">Jam Masuk</p>
                            <p class="text-sm font-bold text-slate-800 leading-tight">{{ $jamMasuk ?? $jadwalMasuk }}</p>
                            @if($shiftLabel)
                                <p class="text-[11px] text-slate-500 leading-tight">{{ $shiftLabel }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="p-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-500 leading-tight">Jam Pulang</p>
                            <p class="text-sm font-bold text-slate-800 leading-tight">{{ $jamPulang ?? ($shiftLabel ? $jadwalPulang : 'Belum Dijadwalkan') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-4 gap-3">
                @php
                    $menu = [
                        ['label' => 'Hadir', 'icon' => 'fa-user-check', 'badge' => $hadir, 'url' => route('history.index')],
                        ['label' => 'Sakit', 'icon' => 'fa-user-injured', 'badge' => 0, 'url' => route('features.show', 'sakit'), 'feature' => 'sakit'],
                        ['label' => 'Izin', 'icon' => 'fa-clipboard-check', 'badge' => $izin, 'url' => route('leave_requests.index')],
                        ['label' => 'Cuti', 'icon' => 'fa-plane-departure', 'badge' => 0, 'url' => route('features.show', 'cuti'), 'feature' => 'cuti'],
                        ['label' => 'ID Card', 'icon' => 'fa-id-card', 'badge' => 0, 'url' => route('profile.index')],
                        ['label' => 'Istirahat', 'icon' => 'fa-mug-hot', 'badge' => 0, 'url' => route('features.show', 'istirahat'), 'feature' => 'istirahat'],
                        ['label' => 'Lembur', 'icon' => 'fa-clock', 'badge' => 0, 'url' => route('features.show', 'lembur'), 'feature' => 'lembur'],
                        ['label' => 'Slip Gaji', 'icon' => 'fa-wallet', 'badge' => 0, 'url' => route('features.show', 'slip_gaji'), 'feature' => 'slip_gaji'],
                        ['label' => 'Jadwal', 'icon' => 'fa-calendar-days', 'badge' => 0, 'url' => route('user.shifts.index')],
                        ['label' => 'Swap Shift', 'icon' => 'fa-right-left', 'badge' => 0, 'url' => route('shift-swaps.index')],
                    ];
                @endphp

                @foreach($menu as $item)
                    @continue(isset($item['feature']) && !($featureSettings[$item['feature']]['user'] ?? false))
                    <a href="{{ $item['url'] ?? '#' }}"
                       class="relative bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm px-2 py-3 text-center active:scale-[0.99] transition">
                        @if(($item['badge'] ?? 0) > 0)
                            <span class="absolute top-2 right-2 w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center">
                                {{ (int) $item['badge'] }}
                            </span>
                        @endif
                        <div class="w-10 h-10 mx-auto rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
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
                    <div class="rounded-xl bg-emerald-50 p-2">
                        <p class="text-sm font-extrabold text-emerald-700">{{ $hadir }}</p>
                        <p class="text-[11px] text-slate-500">Hadir</p>
                    </div>
                    <div class="rounded-xl bg-amber-50 p-2">
                        <p class="text-sm font-extrabold text-amber-700">{{ $telat }}</p>
                        <p class="text-[11px] text-slate-500">Telat</p>
                    </div>
                    <div class="rounded-xl bg-sky-50 p-2">
                        <p class="text-sm font-extrabold text-sky-700">{{ $izin }}</p>
                        <p class="text-[11px] text-slate-500">Izin</p>
                    </div>
                </div>
            </section>

            @if($approvedLeaveToday)
                <section class="bg-sky-50 border border-sky-200 text-sky-900 rounded-2xl p-4 text-sm shadow-sm">
                    Hari ini Anda memiliki izin yang disetujui: <span class="font-bold">{{ ucfirst($approvedLeaveToday->jenis_izin) }}</span>.
                </section>
            @endif

            <section class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">Pengumuman Aktif</p>
                        <p class="text-[11px] text-slate-500">Info terbaru untuk user</p>
                    </div>
                    <a href="{{ route('announcements.index') }}" class="text-xs text-emerald-700 font-semibold">Lihat semua</a>
                </div>
                <div class="mt-3 space-y-3">
                    @forelse($announcements as $announcement)
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-sm font-bold text-slate-800">{{ $announcement->judul }}</p>
                            <p class="text-[11px] text-slate-500 mt-1">{{ $announcement->tanggal_mulai->format('d/m/Y') }} - {{ $announcement->tanggal_berakhir->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-600 mt-2 line-clamp-2">{{ $announcement->isi }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada pengumuman aktif.</p>
                    @endforelse
                </div>
            </section>

            <section class="space-y-3">
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
                            : (in_array($item->status, ['telat', 'terlambat'], true) ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700');
                    @endphp

                    <div class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
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

        <nav class="fixed bottom-0 left-0 w-full flex justify-center z-50 pb-[env(safe-area-inset-bottom)]">
            <div class="w-full max-w-sm h-16 bg-white border-t shadow-xl flex items-center justify-around rounded-t-2xl">
                <a href="{{ route('dashboard') }}" class="text-emerald-700 text-center text-xs">
                    <i class="fa-solid fa-house text-lg"></i>
                    <p>Home</p>
                </a>
                <a href="{{ route('history.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-file-lines text-lg"></i>
                    <p>Histori</p>
                </a>
                <a href="{{ route('absen.page') }}" class="w-14 h-14 -mt-8 bg-emerald-700 text-white rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                    <i class="fa-solid fa-fingerprint text-xl"></i>
                </a>
                <a href="{{ route('leave_requests.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                    <p>Izin</p>
                </a>
                <a href="{{ route('announcements.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-circle-info text-lg"></i>
                    <p>Info</p>
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
</script>
@endsection
