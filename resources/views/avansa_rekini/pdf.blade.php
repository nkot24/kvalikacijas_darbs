<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Avansa rēķins</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 5px; }
        th { background: #f2f2f2; }
        .no-border td { border: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .product-table td, .product-table th { font-size: 10px; }
        hr { border: 0; border-top: 1px solid #000; margin: 10px 0; }
    </style>
</head>
<body>

<h2 class="text-center">AVANSA RĒĶINS Nr. {{ $rekinaNumurs }}</h2>
<p class="text-center">Datums: {{ \Carbon\Carbon::now()->format('d.m.Y') }}</p>

<!-- Nosūtītājs -->
<table class="no-border" style="margin-bottom: 10px;">
    <tr>
        <td><strong>Preču nosūtītājs:</strong> LINDA-1 SIA</td>
        <td class="text-right"><strong>Reģ.Nr.:</strong> 40003167227</td>
    </tr>
    <tr>
        <td><strong>Adrese:</strong> Sabiles iela 2, Kandava, Tukuma novads, LV-3120, Latvija</td>
        <td class="text-right"><strong>PVN Kods:</strong> LV40003167227</td>
    </tr>
    <tr>
        <td><strong>Norēķinu rekvezīti:</strong> A/S SEB banka</td>
        <td class="text-right">
            <span style="white-space: nowrap;">
                kods UNLALV2X &nbsp; konts LV05UNLA0011003467703
            </span>
        </td>
    </tr>
</table>

<hr>

<!-- Saņēmējs -->
<table class="no-border" style="margin-bottom: 4px;">
    <tr>
        <td><strong>Preču saņēmējs:</strong> {{ $client->nosaukums }}</td>
        <td class="text-right"><strong>Reģ.Nr.:</strong> {{ $client->registracijas_numurs }}</td>
    </tr>
    <tr>
        <td><strong>Adrese:</strong> {{ $client->juridiska_adrese }}</td>
        <td class="text-right"><strong>PVN Kods:</strong> {{ $client->pvn_maksataja_numurs ?: '-' }}</td>
    </tr>
</table>

<hr>

{{-- Speciālās atzīmes zem Saņēmējs --}}
@if(!empty($specialNotes))
    <table class="no-border" style="margin-bottom: 10px;">
        <tr>
            <td><strong>Speciālās atzīmes:</strong></td>
        </tr>
        <tr>
            <td>{!! nl2br(e($specialNotes)) !!}</td>
        </tr>
    </table>
@endif

<!-- Preču tabula -->
<table class="product-table">
    <thead>
        <tr>
            <th>Kods</th>
            <th>Nosaukums</th>
            <th>Daudz.</th>
            <th>Merv.</th>
            <th>Cena (bez PVN)</th>
            <th>Summa (bez PVN)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lines as $line)
            <tr>
                <td class="text-center">{{ $line['svitr_kods'] }}</td>
                <td>{{ $line['nosaukums'] }}</td>
                <td class="text-center">{{ $line['qty'] }}</td>
                <td class="text-center">{{ $line['unit'] }}</td>
                <td class="text-right">{{ number_format($line['unit_price_ex_vat'], 2, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($line['sum_ex_vat'], 2, ',', ' ') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Kopsavilkums -->
<table class="no-border" style="margin-top: 15px; width: 45%; float: right;">
    <tr>
        <td class="text-right"><strong>Kopā bez PVN:</strong></td>
        <td class="text-right">{{ number_format($totalExVat, 2, ',', ' ') }} EUR</td>
    </tr>

    @if(!is_null($pvn))
        <tr>
            <td class="text-right"><strong>PVN 21%:</strong></td>
            <td class="text-right">{{ number_format($pvn, 2, ',', ' ') }} EUR</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Starpsumma ar PVN:</strong></td>
            <td class="text-right">{{ number_format($withPvn, 2, ',', ' ') }} EUR</td>
        </tr>
    @else
        <tr>
            <td class="text-right"><strong>Starpsumma (bez PVN):</strong></td>
            <td class="text-right">{{ number_format($withPvn, 2, ',', ' ') }} EUR</td>
        </tr>
    @endif

    @if($useAdvance)
        <tr>
            <td class="text-right"><strong>Avansa maksājums:</strong></td>
            <td class="text-right">{{ number_format($advancePercent, 2, ',', ' ') }} %</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Summa apmaksai (avanss):</strong></td>
            <td class="text-right"><strong>{{ number_format($payable, 2, ',', ' ') }} EUR</strong></td>
        </tr>
    @else
        <tr>
            <td class="text-right"><strong>Pavisam apmaksai:</strong></td>
            <td class="text-right"><strong>{{ number_format($withPvn, 2, ',', ' ') }} EUR</strong></td>
        </tr>
    @endif
</table>

<div style="clear: both;"></div>

<p><i>Summa vārdos:
    @if($useAdvance)
        {{ \App\Helpers\NumberToWords::convertMoney($payable) }}
    @else
        {{ \App\Helpers\NumberToWords::convertMoney($withPvn) }}
    @endif
</i></p>

</body>
</html>
