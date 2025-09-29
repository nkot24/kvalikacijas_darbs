<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class AvansaRekinsController extends Controller
{
    public function create()
    {
        $clients = Client::orderBy('nosaukums')->get();
        return view('avansa_rekini.create', compact('clients'));
    }

    public function getOrders($client_id)
    {
        if ($client_id === 'one_time') {
            $query = Order::query()->whereNull('client_id');
        } else {
            $id = (int) $client_id;
            if (!Client::whereKey($id)->exists()) {
                return response()->json([]);
            }
            $query = Order::query()->where('client_id', $id);
        }

        $orders = $query->with('product')->get()->map(function ($order) {
            $unitPrice =
                optional($order->product)->pardosanas_cena
                ?? optional($order->product)->vairumtirdzniecibas_cena
                ?? optional($order->product)->cena
                ?? null;

            return [
                'id' => $order->id,
                'pasutijuma_numurs' => $order->pasutijuma_numurs ?? 'Bez numura',
                'produkts' => $order->produkts ?? (optional($order->product)->nosaukums ?? 'Nezināms produkts'),
                'daudzums' => $order->daudzums,
                'has_price' => $unitPrice !== null,
            ];
        });

        return response()->json($orders);
    }

    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'orders' => 'required|array|min:1',
            'orders.*' => 'integer',
            'add_pvn' => 'required|in:0,1',
            'order_custom_total' => 'nullable|array',
            'order_custom_total.*' => 'nullable|numeric|min:0',

            'use_advance'     => 'required|in:0,1',
            'advance_percent' => 'nullable|required_if:use_advance,1|numeric|min:0|max:100',

            'special_notes'   => 'nullable|string|max:2000',
            'action'          => 'nullable|in:print,download',
        ]);

        $validator->after(function ($v) use ($request) {
            if ($request->input('client_id') !== 'one_time') {
                if (!Client::whereKey($request->input('client_id'))->exists()) {
                    $v->errors()->add('client_id', 'Norādītais klients neeksistē.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $addVat = $request->add_pvn === '1';

        if ($request->client_id === 'one_time') {
            $client = (object)[
                'id' => 0,
                'nosaukums' => 'Vienreizējs klients',
                'registracijas_numurs' => '',
                'juridiska_adrese' => '',
                'pvn_maksataja_numurs' => '',
            ];

            $orders = Order::whereIn('id', $request->orders)
                ->whereNull('client_id')
                ->with('product')
                ->get();
        } else {
            $client = Client::findOrFail($request->client_id);

            $orders = Order::whereIn('id', $request->orders)
                ->where('client_id', $client->id)
                ->with('product')
                ->get();
        }

        $missingTotals = [];
        foreach ($orders as $order) {
            $unitPrice =
                optional($order->product)->pardosanas_cena
                ?? optional($order->product)->vairumtirdzniecibas_cena
                ?? optional($order->product)->cena
                ?? null;

            if ($unitPrice === null) {
                $customTotal = $request->input("order_custom_total.{$order->id}");
                if ($customTotal === null || $customTotal === '') {
                    $missingTotals[] = $order->id;
                }
            }
        }
        if ($missingTotals) {
            return back()
                ->withErrors(['order_custom_total' => 'Lūdzu ievadiet kopējo cenu pasūtījumiem bez vienības cenas.'])
                ->withInput();
        }

        $lines = [];
        $totalExVat = 0.0;

        foreach ($orders as $order) {
            $product = $order->product;

            $unitPriceNet =
                optional($product)->pardosanas_cena
                ?? optional($product)->vairumtirdzniecibas_cena
                ?? optional($product)->cena
                ?? null;

            if ($unitPriceNet === null) {
                $customTotal = (float) $request->input("order_custom_total.{$order->id}", 0);
                $qty = max(1.0, (float) $order->daudzums);
                $unitPriceNet = $qty > 0 ? ($customTotal / $qty) : 0.0;
            }

            $qty = (float) $order->daudzums;
            $sumExVat = $unitPriceNet * $qty;
            $totalExVat += $sumExVat;

            $lines[] = [
                'svitr_kods' => optional($product)->svitr_kods ?? '-',
                'nosaukums' => optional($product)->nosaukums ?? ($order->produkts ?? '-'),
                'qty' => $order->daudzums,
                'unit' => 'gab.',
                'unit_price_ex_vat' => (float) $unitPriceNet,
                'sum_ex_vat' => $sumExVat,
            ];
        }

        if ($addVat) {
            $pvn = $totalExVat * 0.21;
            $withPvn = $totalExVat + $pvn;
        } else {
            $pvn = null;
            $withPvn = $totalExVat;
        }

        $useAdvance = $request->use_advance === '1';
        $advancePercent = $useAdvance ? (float)$request->advance_percent : null;
        $grandTotal = $withPvn;
        $payable = $useAdvance
            ? round($grandTotal * ($advancePercent / 100), 2)
            : $grandTotal;

        $specialNotes = $request->input('special_notes');

        $orderIds = $orders->pluck('id')->sort()->values();
        $firstId = $orderIds->first() ?? 0;
        $count = $orderIds->count();
        $rekinaNumurs = now()->format('Ymd') . "{$firstId}{$count}";

        $pdf = Pdf::loadView('avansa_rekini.pdf', [
            'client'         => $client,
            'lines'          => $lines,
            'totalExVat'     => $totalExVat,
            'pvn'            => $pvn,
            'withPvn'        => $withPvn,
            'rekinaNumurs'   => $rekinaNumurs,

            'useAdvance'     => $useAdvance,
            'advancePercent' => $advancePercent,
            'payable'        => $payable,
            'specialNotes'   => $specialNotes,
        ])->setPaper('a4');

        return $request->action === 'print'
            ? $pdf->stream('avansa_rekins.pdf')
            : $pdf->download('avansa_rekins.pdf');
    }
}
