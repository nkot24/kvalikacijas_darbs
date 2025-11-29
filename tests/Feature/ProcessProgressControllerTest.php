<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\ProcessProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessProgressControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function login(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_store_creates_process_progress_for_partial_status_with_spent_time(): void
    {
        $user    = $this->login();
        $process = Process::factory()->create();

        $payload = [
            'process_id' => $process->id,
            'status'     => ProcessProgress::STATUS_PARTIAL,
            'spent_time' => 15,
            'comment'    => 'Daļējs progress testam',
        ];

        $response = $this->post(route('process-progress.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Procesa progress ir pievienots.');

        $this->assertDatabaseHas('process_progresses', [
            'process_id' => $process->id,
            'user_id'    => $user->id,
            'status'     => ProcessProgress::STATUS_PARTIAL,
            'spent_time' => 15,
            'comment'    => 'Daļējs progress testam',
        ]);
    }

    public function test_store_requires_spent_time_when_status_is_partial_or_done(): void
    {
        $this->login();
        $process = Process::factory()->create();

        // Missing spent_time for partial status
        $response = $this->from('/back')
            ->post(route('process-progress.store'), [
                'process_id' => $process->id,
                'status'     => ProcessProgress::STATUS_PARTIAL,
                // no spent_time
            ]);

        $response->assertRedirect('/back');
        $response->assertSessionHasErrors(['spent_time']);
    }

    public function test_store_rejects_invalid_status(): void
    {
        $this->login();
        $process = Process::factory()->create();

        $response = $this->post(route('process-progress.store'), [
            'process_id' => $process->id,
            'status'     => 'INVALID_STATUS',
            'spent_time' => 10,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }

    public function test_update_changes_status_spent_time_and_comment(): void
    {
        $user    = $this->login();
        $process = Process::factory()->create();

        $progress = ProcessProgress::create([
            'process_id' => $process->id,
            'user_id'    => $user->id,
            'status'     => ProcessProgress::STATUS_PARTIAL,
            'spent_time' => 10,
            'comment'    => 'Old comment',
        ]);

        $payload = [
            'status'     => ProcessProgress::STATUS_DONE,
            'spent_time' => 25,
            'comment'    => 'Jauns komentārs',
        ];

        $response = $this->put(route('process-progress.update', $progress), $payload);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Procesa progress ir atjaunināts.');

        $this->assertDatabaseHas('process_progresses', [
            'id'         => $progress->id,
            'status'     => ProcessProgress::STATUS_DONE,
            'spent_time' => 25,
            'comment'    => 'Jauns komentārs',
        ]);
    }

    public function test_update_requires_spent_time_when_setting_partial_or_done(): void
    {
        $user    = $this->login();
        $process = Process::factory()->create();

        $progress = ProcessProgress::create([
            'process_id' => $process->id,
            'user_id'    => $user->id,
            'status'     => ProcessProgress::STATUS_PARTIAL,
            'spent_time' => 5,
            'comment'    => null,
        ]);

        $response = $this->from('/back')
            ->put(route('process-progress.update', $progress), [
                'status' => ProcessProgress::STATUS_DONE,
                // no spent_time
            ]);

        $response->assertRedirect('/back');
        $response->assertSessionHasErrors(['spent_time']);
    }

    public function test_destroy_deletes_process_progress(): void
    {
        $user    = $this->login();
        $process = Process::factory()->create();

        $progress = ProcessProgress::create([
            'process_id' => $process->id,
            'user_id'    => $user->id,
            'status'     => ProcessProgress::STATUS_PARTIAL,
            'spent_time' => 10,
            'comment'    => 'To be deleted',
        ]);

        $response = $this->delete(route('process-progress.destroy', $progress));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Procesa progress ir dzēsts.');

        $this->assertDatabaseMissing('process_progresses', [
            'id' => $progress->id,
        ]);
    }
}
