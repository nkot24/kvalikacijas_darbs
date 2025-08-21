<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AvansaRekinsController extends Controller
{
    public function create()
    {
        $clients = Client::all();
        return view('avansa_rekini.create', compact('clients'));
    }

    // JSON API for AJAX: Get orders by client
    public function getOrders($client_id)
    {
        $orders = Order::where('client_id', $client_id)
            ->with('product') // if using related Product model
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'pasutijuma_numurs' => $order->pasutijuma_numurs ?? 'Bez numura',
                    'produkts' => $order->produkts ?? ($order->product->nosaukums ?? 'Nezināms produkts'),
                    'daudzums' => $order->daudzums,
                ];
            });

        return response()->json($orders);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'orders' => 'required|array',
            'price_type' => 'required|in:pardosanas_cena,vairumtirdzniecibas_cena',
            'atlaide_select' => 'required|in:0,1',
            'atlaide' => 'nullable|numeric|min:0|max:100',
        ]);

        $client = Client::findOrFail($request->client_id);
        $orders = Order::whereIn('id', $request->orders)->with('product')->get();

        $priceType = $request->price_type;
        $discount = $request->atlaide_select == '1' ? floatval($request->atlaide) : 0;

        $pdf = Pdf::loadView('avansa_rekini.pdf', [
            'client' => $client,
            'orders' => $orders,
            'priceType' => $priceType,
            'discount' => $discount,
        ])->setPaper('a4');

        switch ($request->action) {
            case 'download':
                return $pdf->download('avansa_rekins.pdf');
            case 'print':
                return $pdf->stream('avansa_rekins.pdf');
            case 'download_print':
                $pdf->save(storage_path('app/public/avansa_rekins.pdf'));
                return $pdf->stream('avansa_rekins.pdf');
            default:
                return back()->with('error', 'Nederīga darbība');
        }
    }
}
