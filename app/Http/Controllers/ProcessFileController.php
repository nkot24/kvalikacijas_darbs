<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProcessFileController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'process_id' => ['required','integer','exists:processes,id'],
            'files.*'    => ['required','file','max:20480'], // 20MB each; adjust as needed
        ], [
            'files.*.max' => 'Fails ir pārāk liels (max 20MB).',
        ]);

        $process = Process::with('users')->findOrFail($data['process_id']);

        // Authorization: user must belong to the process (or be the one assigned via your logic)
        $user = Auth::user();
        $allowed = $process->users?->contains($user) || method_exists($process, 'canUpload') && $process->canUpload($user);
        if (!$allowed) {
            abort(403, 'Nav atļauts augšupielādēt failus šim procesam.');
        }

        foreach ((array) $request->file('files', []) as $file) {
            $storedPath = $file->store('process_files/'.$process->id, 'public'); // storage/app/public/...

            ProcessFile::create([
                'process_id'    => $process->id,
                'uploaded_by'   => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'path'          => $storedPath,
                'mime'          => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Faili veiksmīgi augšupielādēti.');
    }

    public function download(ProcessFile $file)
    {
        $file->load('process.users');
        $user = Auth::user();

        if (!$file->process->users?->contains($user)) {
            abort(403, 'Nav atļauts lejupielādēt šo failu.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function view(ProcessFile $file)
    {
        $file->load('process.users');
        $user = Auth::user();

        if (!$file->process->users?->contains($user)) {
            abort(403, 'Nav atļauts skatīt šo failu.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

        // Try to display inline (PDF/images), otherwise force download
        $mime = $file->mime ?? 'application/octet-stream';
        $inlineTypes = ['application/pdf','image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];

        if (in_array($mime, $inlineTypes, true)) {
            return response()->file(
                Storage::disk('public')->path($file->path),
                ['Content-Type' => $mime]
            );
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function destroy(ProcessFile $file)
    {
        $file->load('process.users');
        $user = Auth::user();

        // Optional: tighten deletion (e.g., only uploader or admin)
        $allowed = $file->uploader?->id === $user->id || $file->process->users?->contains($user);
        if (!$allowed) {
            abort(403, 'Nav atļauts dzēst šo failu.');
        }

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('success', 'Fails dzēsts.');
    }
}
