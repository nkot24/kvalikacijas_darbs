<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Avansa rēķins</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
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

    @php
        // ģenerējam rēķina numuru
        $date = \Carbon\Carbon::now()->format('Ymd');
        $productIdSum = $orders->pluck('products_id')->sum(); // saskaita visus produktu ID
        $rekinaNumurs = $date  . $productIdSum  . $client->id;
    @endphp

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
    </table>

    <hr>

    <!-- Saņēmējs -->
    <table class="no-border" style="margin-bottom: 10px;">
        <tr>
            <td><strong>Preču saņēmējs:</strong> {{ $client->nosaukums }}</td>
            <td class="text-right"><strong>Reģ.Nr.:</strong> {{ $client->registracijas_numurs }}</td>
        </tr>
        <tr>
            <td><strong>Adrese:</strong> {{ $client->juridiska_adrese }}</td>
            <td class="text-right"><strong>PVN Kods:</strong> {{ $client->pvn_maksataja_numurs ?? '-' }}</td>
        </tr>
    </table>

    <!-- Preču tabula -->
    <table class="product-table">
        <thead>
            <tr>
                <th>Kods</th>
                <th>Nosaukums</th>
                <th>Daudz.</th>
                <th>Merv.</th>
                <th>Cena</th>
                <th>Bez atlaides</th>
                <th>Atlaide</th>
                <th>Summa</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total = 0; 
            @endphp
            @foreach($orders as $order)
                @php
                    $product = $order->product ?? null;
                    $price = $product ? $product->$priceType : 0;
                    $sum = $order->daudzums * $price;

                    $bezAtlaides = $sum;
                    $appliedDiscount = $discount ?? 0;
                    if($appliedDiscount > 0){
                        $sum -= ($sum * $appliedDiscount / 100);
                    }
                    $total += $sum;
                @endphp
                <tr>
                    <td class="text-center">{{ $product->svitr_kods ?? '-' }}</td>
                    <td>{{ $product->nosaukums ?? $order->produkts ?? '-' }}</td>
                    <td class="text-center">{{ $order->daudzums }}</td>
                    <td class="text-center">gab.</td>
                    <td class="text-right">{{ number_format($price, 2, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($bezAtlaides, 2, ',', ' ') }}</td>
                    <td class="text-center">{{ $appliedDiscount > 0 ? $appliedDiscount.'%' : '-' }}</td>
                    <td class="text-right">{{ number_format($sum, 2, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $pvn = $total * 0.21;
        $withPvn = $total + $pvn;
    @endphp

    <!-- Kopsavilkums -->
    <table class="no-border" style="margin-top: 15px; width: 40%; float: right;">
        <tr>
            <td class="text-right"><strong>Kopā bez PVN:</strong></td>
            <td class="text-right">{{ number_format($total, 2, ',', ' ') }} EUR</td>
        </tr>
        <tr>
            <td class="text-right"><strong>PVN 21%:</strong></td>
            <td class="text-right">{{ number_format($pvn, 2, ',', ' ') }} EUR</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Pavisam apmaksai:</strong></td>
            <td class="text-right"><strong>{{ number_format($withPvn, 2, ',', ' ') }} EUR</strong></td>
        </tr>
    </table>

    <div style="clear: both;"></div>
    <p><i>Summa vārdos: {{ \App\Helpers\NumberToWords::convertMoney($withPvn) }}</i></p>


</body>
</html>
