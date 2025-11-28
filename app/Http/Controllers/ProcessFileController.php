<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcessFileController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'process_id' => ['required','integer','exists:processes,id'],
            'files'      => ['required','array'],
            'files.*'    => ['file','max:20480'], // 20MB
        ], [
            'files.*.max' => 'Fails ir pārāk liels (max 20MB).',
        ]);

        $process = Process::with('users')->findOrFail($data['process_id']);
        $user    = Auth::user();

        if (! $process->users?->contains($user)
            && ! (method_exists($process, 'canUpload') && $process->canUpload($user))) {
            abort(403, 'Nav atļauts augšupielādēt failus šim procesam.');
        }

        foreach ($request->file('files', []) as $file) {
            if (! $file) continue;

            $storedPath = $file->store("process_files/{$process->id}", 'public');

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
        $this->ensureAccess($file);

        if (! Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function view(ProcessFile $file)
    {
        $this->ensureAccess($file);

        if (! Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

        $mime        = $file->mime ?? 'application/octet-stream';
        $inlineTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ];

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
        $this->ensureAccess($file, true);

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('success', 'Fails dzēsts.');
    }

    private function ensureAccess(ProcessFile $file, bool $forDelete = false): void
{
        $user = Auth::user();
        $file->loadMissing('process.users', 'uploader');

        // 🔹 Admin can do everything
        if ($user && $user->role === 'admin') {
            return;
        }

        $can = $file->process?->users?->contains($user)
            || (int) $file->uploaded_by === (int) $user->id
            || $user->can('process-files.view.any');

        if ($forDelete) {
            $can = $can || $user->can('process-files.delete.any');
        }

        if (! $can) {
            abort(
                403,
                $forDelete
                    ? 'Nav atļauts dzēst šo failu.'
                    : 'Nav atļauts piekļūt šim failu.'
            );
        }
    }
}
