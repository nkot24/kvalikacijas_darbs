<?php

namespace App\Http\Controllers;

use App\Models\ProcessProgress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProcessProgressController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'process_id' => ['required', 'integer', 'exists:processes,id'],
            'status'     => [
                'required',
                'string',
                Rule::in(ProcessProgress::statuses()),
            ],
            'spent_time' => [
                'required_if:status,' . ProcessProgress::STATUS_PARTIAL . ',' . ProcessProgress::STATUS_DONE,
                'nullable',
                'integer',
                'min:1',
            ],
            'comment'    => ['nullable', 'string', 'max:2000'],
        ], [
            'spent_time.required_if' => 'Lūdzu ievadiet pavadīto laiku (minūtēs), ja statuss ir “Daļēji pabeigts” vai “Pabeigts”.',
        ]);

        ProcessProgress::create([
            'process_id' => $validated['process_id'],
            'user_id'    => $request->user()->id,
            'status'     => $validated['status'],
            'spent_time' => $validated['spent_time'] ?? null,
            'comment'    => $validated['comment'] ?? null,
        ]);

        return back()->with('success', 'Procesa progress ir pievienots.');
    }

    public function update(Request $request, ProcessProgress $progress)
    {
        $validated = $request->validate([
            'status'     => [
                'required',
                'string',
                Rule::in(ProcessProgress::statuses()),
            ],
            'spent_time' => [
                'required_if:status,' . ProcessProgress::STATUS_PARTIAL . ',' . ProcessProgress::STATUS_DONE,
                'nullable',
                'integer',
                'min:1',
            ],
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

    public function destroy(ProcessProgress $progress)
    {
        $progress->delete();

        return back()->with('success', 'Procesa progress ir dzēsts.');
    }
}
