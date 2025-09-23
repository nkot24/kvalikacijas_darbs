<?php

namespace App\Http\Controllers;

use App\Models\OrderList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class OrderListController extends Controller
{
    public function index()
    {
        $orderList = OrderList::with('creator')->active()->latest()->get();
        return view('orderList.index', compact('orderList'));
    }

    public function completed(Request $request)
    {
        $query = OrderList::with('creator')->completed();

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('supplier_name', 'like', "%{$term}%");
            });
        }

        $completed = $query->orderByDesc('arrived_at')->orderByDesc('id')->get();

        return view('orderList.completed', [
            'completed' => $completed,
            'q'         => $request->input('q'),
        ]);
    }

    public function create()
    {
        return view('orderList.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'photo'    => 'nullable|mimes:jpeg,jpg,png,webp|max:20480', // 20 MB
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $this->storePhotoMax300KB($request->file('photo'));
        }

        OrderList::create([
            'name'       => $validated['name'],
            'quantity'   => $validated['quantity'],
            'photo_path' => $path,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('orderList.index')->with('success', 'Iepirkums izveidots veiksmīgi.');
    }

    public function edit(OrderList $orderList)
    {
        return view('orderList.edit', ['order' => $orderList]);
    }

    public function update(Request $request, OrderList $orderList)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:255',
            'ordered_at'    => 'nullable|date',
            'expected_at'   => 'nullable|date|after_or_equal:ordered_at',
            'arrived_at'    => 'nullable|date',
            'photo'         => 'nullable|mimes:jpeg,jpg,png,webp|max:20480',
        ]);

        if ($request->hasFile('photo')) {
            if ($orderList->photo_path) {
                Storage::disk('public')->delete($orderList->photo_path);
            }
            $validated['photo_path'] = $this->storePhotoMax300KB($request->file('photo'));
        }

        $orderList->fill($validated)->save();

        return redirect()
            ->route('orderList.index')
            ->with('success', $orderList->status === 'saņemts'
                ? 'Iepirkums atzīmēts kā saņemts. To redzēsi sadaļā "Izpildītie iepirkumi".'
                : 'Iepirkums atjaunināts.'
            );
    }

    public function destroy(OrderList $orderList)
    {
        $orderList->delete();
        return redirect()->route('orderList.index')->with('success', 'Dzēsts veiksmīgi.');
    }

    /**
     * Store uploaded image as JPEG ensuring final size ≤ 300 KB.
     * Works for jpeg, jpg, png, webp.
     */
    private function storePhotoMax300KB(UploadedFile $photo): string
    {
        $image = Image::read($photo->getRealPath());

        // Optional resize logic — remove or adjust if needed
        $maxWidth = 1920; // Optional cap
        if ($image->width() > $maxWidth) {
            $image->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $quality = 90; // Start with high quality
        $maxFileSize = 300 * 1024; // 300KB
        $extension = 'jpg'; // Save as JPEG to control quality

        do {
            $encoded = $image->toJpeg($quality);
            $size = strlen((string) $encoded);
            $quality -= 5;
        } while ($size > $maxFileSize && $quality > 10); // Don’t go below quality 10

        $filename = 'purchases/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

}
