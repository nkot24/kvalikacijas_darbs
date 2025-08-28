<?php

namespace App\Http\Controllers;

use App\Models\ProcessProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProcessProgressController extends Controller
{
    public function __construct()
    {
        // Ensure only authenticated users can change progress.
        $this->middleware('auth');
    }

    /**
     * Store a new progress entry.
     *
     * Expects:
     * - process_id (int, exists: processes.id)
     * - status (string: ieplānots|procesā|daļeji_pabeigts|pabeigts)
     * - spent_time (int, required when status in [daļeji_pabeigts, pabeigts])
     * - comment (string, optional)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'process_id' => ['required', 'integer', 'exists:processes,id'],
            'status'     => [
                'required',
                'string',
                Rule::in(ProcessProgress::statuses()),
            ],
            'spent_time' => ['required_if:status,'.ProcessProgress::STATUS_PARTIAL.','.ProcessProgress::STATUS_DONE, 'nullable', 'integer', 'min:1'],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ], [
            'spent_time.required_if' => 'Lūdzu ievadiet pavadīto laiku (minūtēs), ja statuss ir “Daļēji pabeigts” vai “Pabeigts”.',
        ]);

        ProcessProgress::create([
            'process_id' => $validated['process_id'],
            'user_id'    => Auth::id(),
            'status'     => $validated['status'],
            'spent_time' => $validated['spent_time'] ?? null,
            'comment'    => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Procesa progress ir pievienots.');
    }

    /**
     * Update an existing progress entry.
     *
     * Route model binding will inject the ProcessProgress.
     */
    public function update(Request $request, ProcessProgress $progress)
    {
        // Optional: add authorization here if needed (e.g., policies)
        // $this->authorize('update', $progress);

        $validated = $request->validate([
            'status'     => [
                'required',
                'string',
                Rule::in(ProcessProgress::statuses()),
            ],
            'spent_time' => ['required_if:status,'.ProcessProgress::STATUS_PARTIAL.','.ProcessProgress::STATUS_DONE, 'nullable', 'integer', 'min:1'],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ], [
            'spent_time.required_if' => 'Lūdzu ievadiet pavadīto laiku (minūtēs), ja statuss ir “Daļēji pabeigts” vai “Pabeigts”.',
        ]);

        $progress->update([
            'status'     => $validated['status'],
            'spent_time' => $validated['spent_time'] ?? null,
            'comment'    => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Procesa progress ir atjaunināts.');
    }

    /**
     * Delete a progress entry.
     */
    public function destroy(ProcessProgress $progress)
    {
        // Optional: add authorization here if needed (e.g., policies)
        // $this->authorize('delete', $progress);

        $progress->delete();

        return back()->with('success', 'Procesa progress ir dzēsts.');
    }
}
