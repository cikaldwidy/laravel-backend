<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; }
        th, td { border: 1px solid #333333; padding: 5px; text-align: center; mso-number-format:"\@"; }
        th { background: #b2f5ea; font-weight: bold; }
        .text-left { text-align: left; }
        .head-dark { background: #0f9f9a; color: #ffffff; font-weight: bold; }
        .cell-present { background: #22c55e; color: #ffffff; font-weight: bold; }
        .cell-late { background: #f59e0b; color: #111827; font-weight: bold; }
        .cell-warning { background: #fef08a; color: #713f12; font-weight: bold; }
        .cell-danger, .cell-off { background: #dc2626; color: #ffffff; font-weight: bold; }
        .cell-leave { background: #0ea5e9; color: #ffffff; font-weight: bold; }
        .cell-empty { background: #f1f5f9; color: #64748b; }
        .unit-title { background: #0f9f9a; color: #ffffff; font-weight: bold; font-size: 13px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="{{ count($matrix['dates']) + 5 }}" class="text-left" style="font-size:16px;font-weight:bold;border:0;">Laporan Presensi</td>
        </tr>
        <tr>
            <td colspan="{{ count($matrix['dates']) + 5 }}" class="text-left" style="border:0;">Periode: {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}</td>
        </tr>
        <tr><td colspan="{{ count($matrix['dates']) + 5 }}" style="border:0;"></td></tr>
        <tr>
            @foreach($matrix['legend'] as $legend)
                <td class="{{ $legend['class'] }}">{{ $legend['label'] }}</td>
                <td class="text-left">{{ $legend['text'] }}</td>
            @endforeach
        </tr>
    </table>

    @forelse($matrix['unit_groups'] as $unitGroup)
        <table>
            <tr>
                <td colspan="{{ count($matrix['dates']) + 5 }}" class="unit-title text-left">Unit {{ $unitGroup['unit'] }}</td>
            </tr>
            <tr>
                <td colspan="{{ count($matrix['dates']) + 5 }}" class="text-left">Pegawai: {{ count($unitGroup['employees']) }} | Pagi: {{ $unitGroup['shift_totals']['pagi'] }} | Sore: {{ $unitGroup['shift_totals']['sore'] }} | Malam: {{ $unitGroup['shift_totals']['malam'] }} | Total jam kerja: {{ $unitGroup['total_hours'] }}</td>
            </tr>
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
            <tr><td colspan="{{ count($matrix['dates']) + 5 }}" style="border:0;"></td></tr>
        </table>
    @empty
        <table>
            <tr>
                <td>Belum ada data laporan.</td>
            </tr>
        </table>
    @endforelse
</body>
</html>
