<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class AvansaRekinsController extends Controller
{
    // Rāda veidlapu avansa rēķina izveidei
    public function create()
    {
        $clients = Client::orderBy('nosaukums')->get();
        return view('avansa_rekini.create', compact('clients'));
    }

    // Atgriež klienta vai vienreizējo pasūtījumu sarakstu JSON formātā
    public function getOrders($client_id)
    {
        if ($client_id !== 'one_time' && !Client::whereKey((int)$client_id)->exists()) {
            return response()->json([]);
        }

        $orders = $this->fetchOrders($client_id)->map(fn($order) => $this->formatOrder($order));
        return response()->json($orders);
    }

    // Ģenerē avansa rēķina PDF
    public function generate(Request $request)
    {
        $this->validateRequest($request); // Validācija
        $addVat = $request->add_pvn === '1';
        $client = $this->getClient($request->client_id); // Klients
        $orders = $this->fetchOrders($request->client_id, $request->orders); // Pasūtījumi
        $this->validateMissingTotals($orders, $request); // Pārbauda trūkstošās cenas
        $lines = $this->prepareLines($orders, $request); // Rēķina rindas
        $totalExVat = array_sum(array_column($lines, 'sum_ex_vat'));
        $pvn = $addVat ? $totalExVat * 0.21 : null; // PVN 21%
        $withPvn = $pvn ? $totalExVat + $pvn : $totalExVat;
        $payableData = $this->calculateAdvance($request, $withPvn); // Avansa maksājums
        $rekinaNumurs = $this->generateRekinaNumurs($orders); // Rēķina numurs

        $pdf = Pdf::loadView('avansa_rekini.pdf', array_merge([
            'client' => $client,
            'lines' => $lines,
            'totalExVat' => $totalExVat,
            'pvn' => $pvn,
            'withPvn' => $withPvn,
            'rekinaNumurs' => $rekinaNumurs,
            'specialNotes' => $request->input('special_notes'),
        ], $payableData))->setPaper('a4');

        return $request->action === 'print'
            ? $pdf->stream('avansa_rekins.pdf')
            : $pdf->download('avansa_rekins.pdf');
    }



    // Atgriež pasūtījumus klientam vai vienreizējos
    protected function fetchOrders($client_id, $orderIds = null)
    {
        $query = $client_id === 'one_time'
            ? Order::whereNull('client_id')
            : Order::where('client_id', (int)$client_id);

        if ($orderIds) $query->whereIn('id', $orderIds);
        return $query->with('product')->get();
    }

    // Formē pasūtījuma datus JSON
    protected function formatOrder(Order $order)
    {
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
    }

    // Atgriež klienta objektu vai vienreizēju klientu
    protected function getClient($client_id)
    {
        return $client_id === 'one_time'
            ? (object)[
                'id' => 0, 'nosaukums' => 'Vienreizējs klients',
                'registracijas_numurs' => '', 'juridiska_adrese' => '', 'pvn_maksataja_numurs' => '',
            ]
            : Client::findOrFail($client_id);
    }

    // Validē pieprasījumu
    protected function validateRequest(Request $request)
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

        if ($validator->fails()) back()->withErrors($validator)->withInput()->throwResponse();
    }

    // Pārbauda pasūtījumus bez cenas
    protected function validateMissingTotals($orders, Request $request)
    {
        $missingTotals = $orders->filter(function ($order) use ($request) {
            $unitPrice = $order->product?->pardosanas_cena
                        ?? $order->product?->vairumtirdzniecibas_cena
                        ?? $order->product?->cena;
            return $unitPrice === null && empty($request->input("order_custom_total.{$order->id}"));
        })->pluck('id');

        if ($missingTotals->isNotEmpty()) {
            back()->withErrors(['order_custom_total' => 'Lūdzu ievadiet kopējo cenu pasūtījumiem bez vienības cenas.'])
                 ->withInput()->throwResponse();
        }
    }

    // Sagatavo rēķina rindas
    protected function prepareLines($orders, Request $request)
    {
        $lines = [];
        foreach ($orders as $order) {
            $unitPriceNet = $order->product?->pardosanas_cena
                            ?? $order->product?->vairumtirdzniecibas_cena
                            ?? $order->product?->cena
                            ?? ((float)$request->input("order_custom_total.{$order->id}", 0) / max(1, $order->daudzums));
            $lines[] = [
                'svitr_kods' => $order->product?->svitr_kods ?? '-',
                'nosaukums' => $order->product?->nosaukums ?? $order->produkts ?? '-',
                'qty' => $order->daudzums,
                'unit' => 'gab.',
                'unit_price_ex_vat' => $unitPriceNet,
                'sum_ex_vat' => $unitPriceNet * $order->daudzums,
            ];
        }
        return $lines;
    }

    // Aprēķina avansa maksājumu, ja nepieciešams
    protected function calculateAdvance(Request $request, float $withPvn)
    {
        if ($request->use_advance !== '1') return ['useAdvance'=>false,'advancePercent'=>null,'payable'=>$withPvn];
        $advancePercent = (float)$request->advance_percent;
        return ['useAdvance'=>true,'advancePercent'=>$advancePercent,'payable'=>round($withPvn*($advancePercent/100),2)];
    }

    // Ģenerē rēķina numuru pēc datuma un pasūtījumu ID
    protected function generateRekinaNumurs($orders)
    {
        $orderIds = $orders->pluck('id')->sort()->values();
        return now()->format('Ymd').($orderIds->first() ?? 0).$orderIds->count();
    }
}
