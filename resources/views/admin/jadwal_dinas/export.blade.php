@php
    $monthName = $monthStart->translatedFormat('F');
    $dateKeys = collect($dates)->map(fn ($date) => $date->toDateString())->values();
    $dailyTotals = [];

    foreach ($dateKeys as $dateKey) {
        $dailyTotals[$dateKey] = ['P' => 0, 'S' => 0, 'M' => 0, 'O' => 0, 'TOTAL' => 0];
    }

    foreach ($rows as $row) {
        foreach ($dateKeys as $dateKey) {
            $code = $row['cells'][$dateKey] ?? '';

            if (isset($dailyTotals[$dateKey][$code])) {
                $dailyTotals[$dateKey][$code]++;
            }

            if (in_array($code, ['P', 'S', 'M'], true)) {
                $dailyTotals[$dateKey]['TOTAL']++;
            }
        }
    }

    $border = 'border:1px solid #b7b7b7;';
    $titleStyle = 'background-color:#1f4e79;color:#ffffff;text-align:center;font-weight:bold;font-size:14px;';
    $headerStyle = 'background-color:#ddebf7;text-align:center;font-weight:bold;';
    $dateHeaderStyle = 'background-color:#ddebf7;text-align:center;font-weight:bold;';
    $sundayHeaderStyle = 'background-color:#f4cccc;color:#ff0000;text-align:center;font-weight:bold;';
    $sundayCellStyle = 'background-color:#fce4d6;text-align:center;';
    $normalCellStyle = 'text-align:center;';
    $nameCellStyle = 'text-align:left;';
    $totalLabelStyle = 'background-color:#a9d18e;text-align:center;font-weight:bold;';
    $totalCellStyle = 'background-color:#e2f0d9;text-align:center;font-weight:bold;';
    $noteStyle = 'background-color:#fff2cc;font-weight:bold;';
@endphp

<table>
    <thead>
        <tr>
            <th colspan="{{ count($dates) + 1 }}" style="{{ $titleStyle }}{{ $border }}">
                JADWAL DINAS {{ strtoupper($selectedUnitName) }} BULAN {{ strtoupper($monthName) }} {{ $tahun }}
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="{{ $headerStyle }}{{ $border }}">NAMA</th>
            <th colspan="{{ count($dates) }}" style="{{ $headerStyle }}{{ $border }}">TANGGAL</th>
        </tr>
        <tr>
            @foreach($dates as $date)
                <th style="{{ $date->isSunday() ? $sundayHeaderStyle : $dateHeaderStyle }}{{ $border }}">{{ $date->day }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td style="{{ $nameCellStyle }}{{ $border }}">{{ $row['employee']->name }}</td>
                @foreach($dates as $date)
                    @php($code = $row['cells'][$date->toDateString()] ?? '')
                    <td style="{{ $date->isSunday() ? $sundayCellStyle : $normalCellStyle }}{{ $border }}">{{ $code }}</td>
                @endforeach
            </tr>
        @endforeach

        <tr>
            <td colspan="{{ count($dates) + 1 }}"></td>
        </tr>

        <tr>
            <td style="{{ $totalLabelStyle }}{{ $border }}">TOTAL DINAS</td>
            @foreach($dates as $date)
                <td style="{{ $totalCellStyle }}{{ $border }}">{{ $dailyTotals[$date->toDateString()]['TOTAL'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="{{ $totalLabelStyle }}{{ $border }}">PAGI</td>
            @foreach($dates as $date)
                <td style="{{ $totalCellStyle }}{{ $border }}">{{ $dailyTotals[$date->toDateString()]['P'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="{{ $totalLabelStyle }}{{ $border }}">SORE</td>
            @foreach($dates as $date)
                <td style="{{ $totalCellStyle }}{{ $border }}">{{ $dailyTotals[$date->toDateString()]['S'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="{{ $totalLabelStyle }}{{ $border }}">MALAM</td>
            @foreach($dates as $date)
                <td style="{{ $totalCellStyle }}{{ $border }}">{{ $dailyTotals[$date->toDateString()]['M'] }}</td>
            @endforeach
        </tr>
        <tr>
            <td style="{{ $totalLabelStyle }}{{ $border }}">OFF</td>
            @foreach($dates as $date)
                <td style="{{ $totalCellStyle }}{{ $border }}">{{ $dailyTotals[$date->toDateString()]['O'] }}</td>
            @endforeach
        </tr>

        <tr>
            <td colspan="{{ count($dates) + 1 }}"></td>
        </tr>
        <tr>
            <td style="{{ $noteStyle }}{{ $border }}">Keterangan</td>
            <td colspan="{{ count($dates) }}" style="{{ $noteStyle }}{{ $border }}">P = Pagi, S = Sore, M = Malam, O = Off</td>
        </tr>
    </tbody>
</table>
