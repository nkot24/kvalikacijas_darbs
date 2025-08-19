<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Ražošanas lapa – {{ $order->pasutijuma_numurs }}</title>
    <style>
        @page { size: A4; margin: 15mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color:#000; }
        .row { display:flex; gap:16px; }
        .col { flex:1; }
        .header { margin-bottom: 10px; }
        .box { border:1px solid #000; padding:8px; }
        .title { font-weight:700; font-size:14px; margin-bottom:6px; }
        .kv { display:flex; justify-content:space-between; }
        .kv > div { width:49%; }
        .label { color:#333; }
        table { width:100%; border-collapse: collapse; margin-top:10px; }
        th, td { border:1px solid #000; padding:6px 5px; vertical-align:top; }
        th { text-align:left; background:#f6f6f6; }
        .muted { color:#666; }
        .noprint { margin-bottom:10px; }
        .qr { text-align:right; }
        .bigline { height:42px; } /* room to handwrite */
        @media print { .noprint { display:none } body { margin:0 } }
    </style>
</head>
<body onload="window.print()">

<div class="noprint">
    <button onclick="window.print()">Drukāt</button>
</div>

<div class="header row">
    <div class="col box">
        <div class="title">Pasūtījums</div>
        <div><span class="label">Pasūtījuma Nr:</span> {{ $order->pasutijuma_numurs }}</div>
        <div><span class="label">Datums:</span>
            {{ optional($order->datums)->format('d.m.Y') ?? $order->datums }}
        </div>
        <div><span class="label">Klients:</span> {{ $order->client->nosaukums ?? $order->klients }}</div>
    </div>

    <div class="col box">
        <div class="title">Produkts</div>
        <div><span class="label">Nosaukums:</span> {{ $order->product->nosaukums ?? $order->produkts }}</div>
        <div><span class="label">Kods:</span> {{ $order->product->svitr_kods ?? '—' }}</div>
        <div class="kv">
            <div><span class="label">Skaits:</span> {{ $order->daudzums }}</div>
            <div><span class="label">Prioritāte:</span> {{ $order->prioritāte }}</div>
        </div>
    </div>

    <div class="col box qr">
        {{-- QR code that opens this order’s page --}}
        {!! QrCode::size(130)->margin(0)->generate(route('orders.show', $order)) !!}
        <div class="muted">Skenē, lai atvērtu pasūtījumu</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:28%">Procesi</th>
            <th>Darbinieks</th>
            <th>Darba Laiks</th>
            <th>Komentāri</th>
            <th>Komentāri</th>
            <th style="width:10%">Statuss</th>
        </tr>
    </thead>
    <tbody>
        @php
            $procesi = [
                'Rasēšana','Loksņu griešana','Cauruļu griešana','Locīšana',
                'Metināšana','Slīpēšana','Montāža','Krāsošana','Pakošana',
            ];
        @endphp
        @foreach($procesi as $p)
            <tr class="bigline">
                <td>{{ $p }}</td>
                <td></td><td></td><td></td><td></td>
                <td></td>
            </tr>
        @endforeach
    </tbody>
</table>

<table style="margin-top:10px">
    <tbody>
        <tr>
            <th style="width:25%">Materiāli</th>
            <td class="bigline"></td>
        </tr>
        <tr>
            <th>Piezīmes</th>
            <td class="bigline">{{ $order->piezimes }}</td>
        </tr>
    </tbody>
</table>

</body>
</html>
