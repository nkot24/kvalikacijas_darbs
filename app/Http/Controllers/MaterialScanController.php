<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MaterialScan;

class MaterialScanController extends Controller
{
    public function scanView()
    {
        return view('inventory.materials-scan');
    }

    public function storeScan(Request $request)
    {
        $data = $request->validate([
            'svitr_kods' => ['required', 'string'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $scan = MaterialScan::create([
            'svitr_kods' => trim($data['svitr_kods']),
            'qty' => $data['qty'],
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Materiāls saglabāts.',
            'scan' => $scan,
        ]);
    }

    public function index(Request $request)
    {
        $q = $request->input('q');
        $onlyNotAccounted = (bool)$request->boolean('only_not_accounted');

        $scans = MaterialScan::with('creator')
            ->when($q, fn($qq) => $qq->where('svitr_kods', 'like', "%$q%"))
            ->when($onlyNotAccounted, fn($qq) => $qq->where('accounted', false))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('inventory.materials-list', compact('scans', 'q', 'onlyNotAccounted'));
    }

    public function bulkAccount(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:material_scans,id'],
            'accounted' => ['nullable', 'boolean'],
        ]);

        $accounted = $data['accounted'] ?? true;

        MaterialScan::whereIn('id', $data['ids'])
            ->update([
                'accounted' => $accounted,
                'accounted_at' => $accounted ? now() : null,
            ]);

        return response()->json(['ok' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:material_scans,id'],
        ])['ids'];

        MaterialScan::whereIn('id', $ids)->delete();

        return response()->json(['ok' => true, 'message' => 'Ieraksti dzēsti.']);
    }
}
