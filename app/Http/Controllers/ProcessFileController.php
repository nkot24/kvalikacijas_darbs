<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Process;
use App\Models\ProcessFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProcessFileController extends Controller
{
    /**
     * Upload files for a process.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'process_id' => ['required','integer','exists:processes,id'],
            'files.*'    => ['required','file','max:20480'], // 20MB per file
        ], [
            'files.*.max' => 'Fails ir pārāk liels (max 20MB).',
        ]);

        $process = Process::with('users')->findOrFail($data['process_id']);

        // Authorization: user must belong to the process (or pass custom upload gate on Process)
        $user = Auth::user();
        $allowed = $process->users?->contains($user)
            || (method_exists($process, 'canUpload') && $process->canUpload($user));

        if (!$allowed) {
            abort(403, 'Nav atļauts augšupielādēt failus šim procesam.');
        }

        foreach ((array) $request->file('files', []) as $file) {
            $storedPath = $file->store('process_files/'.$process->id, 'public');

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

    /**
     * Download a file (forces download).
     */
    public function download(ProcessFile $file)
    {
        // Only load relations that exist
        $file->load('process.users', 'uploader');

        if (!$this->userCanAccessFile($file, Auth::user())) {
            abort(403, 'Nav atļauts lejupielādēt šo failu.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    /**
     * View a file inline when possible (PDF/images), otherwise download.
     */
    public function view(ProcessFile $file)
    {
        $file->load('process.users', 'uploader');

        if (!$this->userCanAccessFile($file, Auth::user())) {
            abort(403, 'Nav atļauts skatīt šo failu.');
        }

        if (!Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

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

    /**
     * Delete a file.
     * Adjust the rule as needed (here: uploader, process users, or broad permission).
     */
    public function destroy(ProcessFile $file)
    {
        $file->load('process.users', 'uploader');

        $user = Auth::user();
        $allowed = ($file->uploader?->id === $user->id)
            || $file->process->users?->contains($user)
            || $user->can('process-files.delete.any'); // optional global permission

        if (!$allowed) {
            abort(403, 'Nav atļauts dzēst šo failu.');
        }

        Storage::disk('public')->delete($file->path);
        $file->delete();

        return back()->with('success', 'Fails dzēsts.');
    }

    /**
     * Centralized access rule used by view() and download():
     * allow if user is:
     *  - in process users, OR
     *  - the uploader, OR
     *  - allowed to view the parent order (OrderPolicy@view), OR
     *  - has a broad permission like process-files.view.any
     */
    private function userCanAccessFile(ProcessFile $file, $user): bool
    {
        // 1) Process participant?
        if ($file->process?->users?->contains($user)) {
            return true;
        }

        // 2) Uploader can always access
        if ((int) $file->uploaded_by === (int) $user->id) {
            return true;
        }

        // 3) Can view the parent order? (OrderPolicy@view)
        // We resolve the order by finding any Order that owns a Production
        // which has a Task for this file's process_id.
        if ($order = $this->findOrderForFile($file)) {
            if (Gate::allows('view', $order)) {
                return true;
            }
        }

        // 4) Optional broad permission
        if ($user->can('process-files.view.any')) {
            return true;
        }

        return false;
    }

    /**
     * Resolve the parent Order for a given file without requiring a Process->production relation.
     * Assumes:
     *  - Order hasOne Production (or hasMany, but at least one exists)
     *  - Production hasMany Tasks
     *  - Task has process_id referencing the Process where the file lives
     */
    private function findOrderForFile(ProcessFile $file): ?Order
    {
        return Order::query()
            ->whereHas('production.tasks', function ($q) use ($file) {
                $q->where('process_id', $file->process_id);
            })
            ->first();
    }
}
