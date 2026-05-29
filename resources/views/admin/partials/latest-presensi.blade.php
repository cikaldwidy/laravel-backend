@php
    $latestBadgeClass = [
        'hadir'        => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        'telat'        => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'terlambat'    => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'izin'         => 'bg-sky-50 text-sky-700 ring-1 ring-sky-100',
        'normal'       => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        'pulang_cepat' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100',
    ];
    $latestStart = $latestPresensiTotal > 0 ? (($latestPage - 1) * $latestPerPage) + 1 : 0;
    $latestEnd = $latestPresensiTotal > 0 ? min($latestStart + $latestPresensiRows->count() - 1, $latestPresensiTotal) : 0;
    $latestQueryBase = [
        'tanggal' => $tanggal,
        'chart_period' => $chartPeriod,
        'latest_year' => $latestYear,
    ];
    $pageStart = max(1, $latestPage - 2);
    $pageEnd = min($latestTotalPages, $pageStart + 4);
    $pageStart = max(1, $pageEnd - 4);
@endphp

<article id="latestPresensiCard" class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm" aria-live="polite" data-latest-presensi-card>
    <div class="flex flex-col gap-3 border-b border-blue-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-sm font-bold text-gray-700">Presensi Terbaru</h2>
        <form method="GET" action="{{ route('admin.dashboard') }}" data-latest-presensi-form>
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="chart_period" value="{{ $chartPeriod }}">
            <input type="hidden" name="latest_page" value="1">
            <label class="relative block">
                <select
                    name="latest_year"
                    class="dashboard-select h-9 appearance-none rounded-md border border-blue-100 bg-blue-50 pl-3 pr-8 text-xs font-black text-blue-700 outline-none transition hover:text-gray-700 focus:border-blue-500 focus:text-gray-700 focus:ring-4 focus:ring-blue-500/10"
                >
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" @selected((int) $latestYear === (int) $year)>{{ $year }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-blue-500"></i>
            </label>
        </form>
    </div>
    <div class="flex flex-col gap-3 border-b border-blue-50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.dashboard.latest-presensi.export', ['year' => $latestYear]) }}"
           class="inline-flex h-8 w-fit items-center gap-1.5 rounded-md border border-blue-100 bg-white px-3 text-xs font-black text-gray-700 no-underline transition hover:bg-blue-50 hover:text-blue-700">
            <i class="fa-solid fa-file-excel text-[11px] text-emerald-600"></i>
            XLSX
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-blue-50/40 text-[11px] font-black uppercase tracking-wide text-blue-800">
                <tr>
                    <th class="px-4 py-3 text-left">No</th>
                    <th class="px-4 py-3 text-left">Nama Pegawai</th>
                    <th class="px-4 py-3 text-left">Unit Kerja/Jabatan</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-blue-50">
                @forelse($latestPresensiRows as $row)
                    @php
                        $latestStatus = $row['status'];
                        $latestLabel = $row['label'];
                        $latestBadge = $latestBadgeClass[$latestStatus] ?? 'bg-gray-100 text-gray-600 ring-1 ring-gray-200';
                        $employeeDetail = $row['user']?->employeeDetail;
                        $unitName = $employeeDetail?->department?->nama_departemen ?? $employeeDetail?->departemen ?? '-';
                        $positionName = $employeeDetail?->position?->nama_jabatan ?? $employeeDetail?->jabatan ?? '-';
                    @endphp
                    <tr class="hover:bg-blue-50/40">
                        <td class="px-4 py-3 font-semibold text-gray-500">{{ $latestStart + $loop->index }}</td>
                        <td class="px-4 py-3 font-bold text-gray-700">{{ $row['user']?->name ?? 'User dihapus' }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-600">
                            <div class="min-w-40 leading-tight">
                                <p class="font-bold text-gray-700">{{ $unitName }}</p>
                                <p class="mt-0.5 text-xs font-semibold text-gray-500">{{ $positionName }}</p>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-600">{{ $row['tanggal']->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-600">{{ $row['waktu']->format('H:i:s') }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-600">{{ $row['jenis'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex min-w-[96px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-[11px] font-black leading-none {{ $latestBadge }}">
                                {{ $latestLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-sm font-semibold text-gray-400">Belum ada presensi terbaru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="flex flex-col gap-3 border-t border-blue-50 px-5 py-3 text-xs font-semibold text-gray-500 sm:flex-row sm:items-center sm:justify-between">
        <span>Menampilkan {{ $latestStart }}-{{ $latestEnd }} dari {{ $latestPresensiTotal }} data</span>
        <div class="flex items-center gap-1">
            <a href="{{ $latestPage > 1 ? route('admin.dashboard', $latestQueryBase + ['latest_page' => $latestPage - 1]) : '#' }}"
               data-latest-presensi-page
               class="dashboard-page-link inline-flex h-8 items-center rounded-md border border-blue-100 px-3 text-xs font-semibold no-underline {{ $latestPage > 1 ? 'text-gray-600 hover:bg-blue-50 hover:text-gray-700' : 'pointer-events-none text-gray-300' }}">
                &lt;&lt;
            </a>
            @for($page = $pageStart; $page <= $pageEnd; $page++)
                <a href="{{ route('admin.dashboard', $latestQueryBase + ['latest_page' => $page]) }}"
                   data-latest-presensi-page
                   class="dashboard-page-link inline-flex h-8 min-w-8 items-center justify-center rounded-md border border-blue-100 px-2 text-xs font-semibold no-underline {{ $page === $latestPage ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50 hover:text-gray-700' }}">
                    {{ $page }}
                </a>
            @endfor
            <a href="{{ $latestPage < $latestTotalPages ? route('admin.dashboard', $latestQueryBase + ['latest_page' => $latestPage + 1]) : '#' }}"
               data-latest-presensi-page
               class="dashboard-page-link inline-flex h-8 items-center rounded-md border border-blue-100 px-3 text-xs font-semibold no-underline {{ $latestPage < $latestTotalPages ? 'text-gray-600 hover:bg-blue-50 hover:text-gray-700' : 'pointer-events-none text-gray-300' }}">
                &gt;&gt;
            </a>
        </div>
    </div>
</article>
