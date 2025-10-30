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
        $query = $client_id === 'one_time'
            ? Order::whereNull('client_id')
            : Order::where('client_id', (int) $client_id);

        if ($client_id !== 'one_time' && !Client::whereKey((int)$client_id)->exists()) {
            return response()->json([]);
        }

        $orders = $query->with('product')->get()->map(function ($order) {
            $unitPrice = $order->product?->pardosanas_cena
                         ?? $order->product?->vairumtirdzniecibas_cena
                         ?? $order->product?->cena;

            return [
                'id' => $order->id,
                'pasutijuma_numurs' => $order->pasutijuma_numurs ?? 'Bez numura',
                'produkts' => $order->produkts ?? $order->product?->nosaukums ?? 'Nezināms produkts',
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
            'use_advance' => 'required|in:0,1',
            'advance_percent' => 'nullable|required_if:use_advance,1|numeric|min:0|max:100',
            'special_notes' => 'nullable|string|max:2000',
            'action' => 'nullable|in:print,download',
        ]);

        $validator->after(function ($v) use ($request) {
            if ($request->client_id !== 'one_time' && !Client::whereKey($request->client_id)->exists()) {
                $v->errors()->add('client_id', 'Norādītais klients neeksistē.');
            }
        });

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        $addVat = $request->add_pvn === '1';

        $client = $request->client_id === 'one_time'
            ? (object)[
                'id' => 0,
                'nosaukums' => 'Vienreizējs klients',
                'registracijas_numurs' => '',
                'juridiska_adrese' => '',
                'pvn_maksataja_numurs' => '',
            ]
            : Client::findOrFail($request->client_id);

        $orders = Order::whereIn('id', $request->orders)
            ->when($request->client_id !== 'one_time', fn($q) => $q->where('client_id', $client->id))
            ->when($request->client_id === 'one_time', fn($q) => $q->whereNull('client_id'))
            ->with('product')
            ->get();

        $missingTotals = $orders->filter(function ($order) use ($request) {
            $unitPrice = $order->product?->pardosanas_cena
                        ?? $order->product?->vairumtirdzniecibas_cena
                        ?? $order->product?->cena;

            return $unitPrice === null && empty($request->input("order_custom_total.{$order->id}"));
        })->pluck('id');

        if ($missingTotals->isNotEmpty()) {
            return back()
                ->withErrors(['order_custom_total' => 'Lūdzu ievadiet kopējo cenu pasūtījumiem bez vienības cenas.'])
                ->withInput();
        }

        $lines = [];
        $totalExVat = 0.0;

        foreach ($orders as $order) {
            $unitPriceNet = $order->product?->pardosanas_cena
                            ?? $order->product?->vairumtirdzniecibas_cena
                            ?? $order->product?->cena
                            ?? ((float)$request->input("order_custom_total.{$order->id}", 0) / max(1, $order->daudzums));

            $sumExVat = $unitPriceNet * $order->daudzums;
            $totalExVat += $sumExVat;

            $lines[] = [
                'svitr_kods' => $order->product?->svitr_kods ?? '-',
                'nosaukums' => $order->product?->nosaukums ?? $order->produkts ?? '-',
                'qty' => $order->daudzums,
                'unit' => 'gab.',
                'unit_price_ex_vat' => $unitPriceNet,
                'sum_ex_vat' => $sumExVat,
            ];
        }

        $pvn = $addVat ? $totalExVat * 0.21 : null;
        $withPvn = $pvn ? $totalExVat + $pvn : $totalExVat;

        $useAdvance = $request->use_advance === '1';
        $advancePercent = $useAdvance ? (float)$request->advance_percent : null;
        $payable = $useAdvance ? round($withPvn * ($advancePercent / 100), 2) : $withPvn;

        $orderIds = $orders->pluck('id')->sort()->values();
        $rekinaNumurs = now()->format('Ymd') . ($orderIds->first() ?? 0) . $orderIds->count();

        $pdf = Pdf::loadView('avansa_rekini.pdf', [
            'client' => $client,
            'lines' => $lines,
            'totalExVat' => $totalExVat,
            'pvn' => $pvn,
            'withPvn' => $withPvn,
            'rekinaNumurs' => $rekinaNumurs,
            'useAdvance' => $useAdvance,
            'advancePercent' => $advancePercent,
            'payable' => $payable,
            'specialNotes' => $request->input('special_notes'),
        ])->setPaper('a4');

        return $request->action === 'print'
            ? $pdf->stream('avansa_rekins.pdf')
            : $pdf->download('avansa_rekins.pdf');
    }
}
