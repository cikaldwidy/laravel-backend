<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            font-family: Calibri, Arial, sans-serif;
            font-size: 11px;
            mso-displayed-decimal-separator: "\.";
            mso-displayed-thousand-separator: "\,";
        }

        th,
        td {
            border: 1px solid #b7b7b7;
            padding: 4px 6px;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
            mso-number-format: "\@";
        }

        th {
            background: #ddebf7;
            color: #000000;
            font-weight: bold;
        }

        .title {
            background: #1f4e79;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            background: #ddebf7;
            font-weight: bold;
            text-align: left;
        }

        .text-left { text-align: left; }
        .no-border { border: 0; }
        .name-cell { width: 230px; text-align: left; }
        .date-cell { width: 34px; }
        .summary-cell { width: 70px; }
        .hours-cell { width: 86px; }
        .head-dark { background: #0f766e; color: #ffffff; font-weight: bold; }
        .unit-title { background: #0f766e; color: #ffffff; font-weight: bold; text-align: left; }
        .unit-summary { background: #e2f0d9; font-weight: bold; text-align: left; }
        .footer-label { background: #a9d18e; font-weight: bold; text-align: left; }
        .footer-total { background: #e2f0d9; font-weight: bold; }
        .sunday-header { background: #f4cccc; color: #ff0000; font-weight: bold; }
        .cell-present { background: #22c55e; color: #ffffff; font-weight: bold; }
        .cell-late { background: #f59e0b; color: #111827; font-weight: bold; }
        .cell-warning { background: #fef08a; color: #713f12; font-weight: bold; }
        .cell-danger,
        .cell-off { background: #dc2626; color: #ffffff; font-weight: bold; }
        .cell-leave { background: #0ea5e9; color: #ffffff; font-weight: bold; }
        .cell-empty { background: #f1f5f9; color: #64748b; }
        .sunday-cell { background: #fce4d6; }
        .legend-title { background: #fff2cc; font-weight: bold; text-align: left; }
        .legend-text { background: #fff2cc; text-align: left; }
    </style>
</head>
<body>
@php
    $dateCount = count($matrix['dates']);
    $colspan = $dateCount + 5;
@endphp

<table>
    <tr>
        <td colspan="{{ $colspan }}" class="title">LAPORAN PRESENSI</td>
    </tr>
    <tr>
        <td colspan="{{ $colspan }}" class="subtitle">Periode: {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td colspan="{{ $colspan }}" class="no-border"></td>
    </tr>
    <tr>
        <td class="legend-title">Keterangan</td>
        <td colspan="{{ $colspan - 1 }}" class="legend-text">
            @foreach($matrix['legend'] as $legend)
                {{ $legend['label'] }} = {{ $legend['text'] }}{{ !$loop->last ? ', ' : '' }}
            @endforeach
        </td>
    </tr>
    <tr>
        <td colspan="{{ $colspan }}" class="no-border"></td>
    </tr>
</table>

@forelse($matrix['unit_groups'] as $unitGroup)
    <table>
        <tr>
            <td colspan="{{ $colspan }}" class="unit-title">Unit {{ $unitGroup['unit'] }}</td>
        </tr>
        <tr>
            <td colspan="{{ $colspan }}" class="unit-summary">
                Pegawai: {{ count($unitGroup['employees']) }}
                | Pagi: {{ $unitGroup['shift_totals']['pagi'] }}
                | Sore: {{ $unitGroup['shift_totals']['sore'] }}
                | Malam: {{ $unitGroup['shift_totals']['malam'] }}
                | Total jam kerja: {{ $unitGroup['total_hours'] }}
            </td>
        </tr>
        <tr>
            <th rowspan="2" class="name-cell">Nama / Tanggal</th>
            @foreach($matrix['dates'] as $date)
                <th class="date-cell {{ $date->isSunday() ? 'sunday-header' : '' }}">{{ $date->translatedFormat('D') }}</th>
            @endforeach
            <th colspan="3" class="head-dark">Jumlah Grup Karyawan / Shift</th>
            <th rowspan="2" class="head-dark hours-cell">Total Jam Kerja</th>
        </tr>
        <tr>
            @foreach($matrix['dates'] as $date)
                <th class="date-cell {{ $date->isSunday() ? 'sunday-header' : '' }}">{{ $date->format('j') }}</th>
            @endforeach
            <th class="head-dark summary-cell">Pagi</th>
            <th class="head-dark summary-cell">Sore</th>
            <th class="head-dark summary-cell">Malam</th>
        </tr>
        @foreach($unitGroup['employees'] as $employee)
            <tr>
                <td class="name-cell">{{ $employee['name'] }}</td>
                @foreach($matrix['dates'] as $date)
                    @php($cell = $employee['cells'][$date->toDateString()])
                    <td class="date-cell {{ $cell['class'] }} {{ $date->isSunday() && $cell['class'] === 'cell-empty' ? 'sunday-cell' : '' }}">{{ $cell['label'] }}</td>
                @endforeach
                <td class="summary-cell">{{ $employee['shift_totals']['pagi'] }}</td>
                <td class="summary-cell">{{ $employee['shift_totals']['sore'] }}</td>
                <td class="summary-cell">{{ $employee['shift_totals']['malam'] }}</td>
                <td class="hours-cell">{{ $employee['total_hours'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td class="footer-label">Total Dinas</td>
            @foreach($matrix['dates'] as $date)
                <td class="footer-total">{{ $unitGroup['daily_totals'][$date->toDateString()]['pagi'] + $unitGroup['daily_totals'][$date->toDateString()]['sore'] + $unitGroup['daily_totals'][$date->toDateString()]['malam'] }}</td>
            @endforeach
            <td colspan="4"></td>
        </tr>
        @foreach(['pagi' => 'Shift Pagi', 'sore' => 'Shift Sore', 'malam' => 'Shift Malam'] as $key => $label)
            <tr>
                <td class="footer-label">{{ $label }}</td>
                @foreach($matrix['dates'] as $date)
                    <td class="footer-total">{{ $unitGroup['daily_totals'][$date->toDateString()][$key] }}</td>
                @endforeach
                <td colspan="4"></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="{{ $colspan }}" class="no-border"></td>
        </tr>
    </table>
@empty
    <table>
        <tr>
            <td class="text-left">Belum ada data laporan.</td>
        </tr>
    </table>
@endforelse
</body>
</html>
