<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111827; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        p { margin: 0 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #374151; padding: 4px; text-align: center; }
        th { background: #b2f5ea; }
        .text-left { text-align: left; }
        .head-dark { background: #0f9f9a; color: #fff; }
        .cell-present { background: #22c55e; color: #fff; font-weight: bold; }
        .cell-late { background: #f59e0b; color: #111827; font-weight: bold; }
        .cell-warning { background: #fef08a; color: #713f12; font-weight: bold; }
        .cell-danger, .cell-off { background: #dc2626; color: #fff; font-weight: bold; }
        .cell-leave { background: #0ea5e9; color: #fff; font-weight: bold; }
        .cell-empty { background: #f1f5f9; color: #64748b; }
        .legend { display: flex; flex-wrap: wrap; gap: 6px; margin: 10px 0; }
        .legend-item { display: inline-block; margin-right: 8px; margin-bottom: 4px; }
        .legend-code { display: inline-block; min-width: 24px; padding: 4px; text-align: center; border: 1px solid #374151; }
        .unit-title { margin-top: 14px; padding: 6px; background: #0f9f9a; color: #fff; font-weight: bold; }
        .unit-subtitle { margin: 4px 0 0; font-size: 9px; }
        @media print {
            @page { size: landscape; margin: 10mm; }
            .unit-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body onload="window.print()">
    <h1>Laporan Presensi</h1>
    <p>Periode: {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}</p>

    <div class="legend">
        @foreach($matrix['legend'] as $legend)
            <span class="legend-item">
                <span class="legend-code {{ $legend['class'] }}">{{ $legend['label'] }}</span>
                {{ $legend['text'] }}
            </span>
        @endforeach
    </div>

    @forelse($matrix['unit_groups'] as $unitGroup)
        <div class="unit-section">
            <div class="unit-title">Unit Kerja/Bagian {{ $unitGroup['unit'] }}</div>
            <p class="unit-subtitle">{{ count($unitGroup['employees']) }} pegawai | Pagi {{ $unitGroup['shift_totals']['pagi'] }} | Sore {{ $unitGroup['shift_totals']['sore'] }} | Malam {{ $unitGroup['shift_totals']['malam'] }} | {{ $unitGroup['total_hours'] }} total jam kerja</p>

            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="text-left">Nama / Tanggal</th>
                        @foreach($matrix['dates'] as $date)
                            <th>{{ $date->translatedFormat('D') }}</th>
                        @endforeach
                        <th colspan="3" class="head-dark">Jumlah Grup Karyawan / Shift</th>
                        <th rowspan="2" class="head-dark">Total Jam Kerja</th>
                    </tr>
                    <tr>
                        @foreach($matrix['dates'] as $date)
                            <th>{{ $date->format('j') }}</th>
                        @endforeach
                        <th class="head-dark">Shift Pagi</th>
                        <th class="head-dark">Shift Sore</th>
                        <th class="head-dark">Shift Malam</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unitGroup['employees'] as $employee)
                        <tr>
                            <td class="text-left">{{ $employee['name'] }}</td>
                            @foreach($matrix['dates'] as $date)
                                @php($cell = $employee['cells'][$date->toDateString()])
                                <td class="{{ $cell['class'] }}">{{ $cell['label'] }}</td>
                            @endforeach
                            <td>{{ $employee['shift_totals']['pagi'] }}</td>
                            <td>{{ $employee['shift_totals']['sore'] }}</td>
                            <td>{{ $employee['shift_totals']['malam'] }}</td>
                            <td>{{ $employee['total_hours'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="head-dark text-left">Jumlah Grup Karyawan / Shift (hari)</td>
                        @foreach($matrix['dates'] as $date)
                            <td class="head-dark">{{ $unitGroup['daily_totals'][$date->toDateString()]['pagi'] + $unitGroup['daily_totals'][$date->toDateString()]['sore'] + $unitGroup['daily_totals'][$date->toDateString()]['malam'] }}</td>
                        @endforeach
                        <td colspan="4"></td>
                    </tr>
                    @foreach(['pagi' => 'Shift Pagi', 'sore' => 'Shift Sore', 'malam' => 'Shift Malam'] as $key => $label)
                        <tr>
                            <td class="text-left">{{ $label }}</td>
                            @foreach($matrix['dates'] as $date)
                                <td>{{ $unitGroup['daily_totals'][$date->toDateString()][$key] }}</td>
                            @endforeach
                            <td colspan="4"></td>
                        </tr>
                    @endforeach
                </tfoot>
            </table>
        </div>
    @empty
        <p>Belum ada data laporan.</p>
    @endforelse
</body>
</html>
