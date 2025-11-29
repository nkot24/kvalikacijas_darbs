<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Process;
use App\Models\Production;
use App\Models\ProcessProgress;
use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function login(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_index_splits_personal_tasks_into_current_and_future(): void
    {
        $user = $this->login();

        // Order + production
        $order = Order::factory()->create([
            'prioritāte'      => 'augsta',
            'izpildes_datums' => now()->addDay()->toDateString(),
            'daudzums'        => 10,
        ]);

        $production = Production::factory()->create([
            'order_id' => $order->id,
        ]);

        // Two processes with different IDs so sortKeys() works
        $process1 = Process::factory()->create(); // will be "unlocked"
        $process2 = Process::factory()->create();

        // Personal tasks for the same production
        $taskCurrent = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process1->id,
            'user_id'       => $user->id,
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ]);

        $taskFuture = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process2->id,
            'user_id'       => $user->id,
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ]);

        $response = $this->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tasks.index');

        $currentTasks = $response->viewData('currentTasks');
        $futureTasks  = $response->viewData('futureTasks');

        $this->assertTrue(
            $currentTasks->contains('id', $taskCurrent->id),
            'Current tasks should contain the unlocked task'
        );

        $this->assertTrue(
            $futureTasks->contains('id', $taskFuture->id),
            'Future tasks should contain the locked task'
        );
    }

    public function test_update_personal_task_partial_progress_creates_logs_and_process_progress(): void
    {
        $user = $this->login();

        $order = Order::factory()->create([
            'daudzums' => 10,
        ]);

        $production = Production::factory()->create([
            'order_id' => $order->id,
        ]);

        $process = Process::factory()->create();

        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id,
            'user_id'       => $user->id,
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ]);

        $payload = [
            'status'      => 'daļēji pabeigts',
            'done_amount' => 4,         // delta
            'spent_time'  => 1.5,
            'comment'     => 'Progress test',
        ];

        $response = $this->put(route('tasks.update', $task), $payload);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Uzdevums atjaunināts.');

        // Task updated
        $this->assertDatabaseHas('tasks', [
            'id'          => $task->id,
            'status'      => 'daļēji pabeigts',
            'done_amount' => 4,
        ]);

        // TaskWorkLog created with delta
        $this->assertDatabaseHas('task_work_logs', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'amount'  => 4,
        ]);

        // ProcessProgress created (⚠ table name FIXED here)
        $this->assertDatabaseHas('process_progresses', [
            'task_id'    => $task->id,
            'process_id' => $process->id,
            'user_id'    => $user->id,
            'status'     => 'daļēji pabeigts',
            'spent_time' => 1.5,
            'comment'    => 'Progress test',
        ]);
    }

    public function test_update_sets_status_to_finished_when_done_amount_reaches_order_quantity(): void
    {
        $user = $this->login();

        $order = Order::factory()->create([
            'daudzums' => 5,
        ]);

        $production = Production::factory()->create([
            'order_id' => $order->id,
        ]);

        $process = Process::factory()->create();

        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id,
            'user_id'       => $user->id,
            'status'        => 'daļēji pabeigts',
            'done_amount'   => 3,
        ]);

        $payload = [
            'status'      => 'daļēji pabeigts',
            'done_amount' => 3,        // delta => 3 + 3 = 6, but capped to 5
            'spent_time'  => 2.0,
            'comment'     => null,
        ];

        $response = $this->put(route('tasks.update', $task), $payload);

        $response->assertRedirect(route('tasks.index'));

        // Should be capped at order.daudzums and status flipped to pabeigts
        $this->assertDatabaseHas('tasks', [
            'id'          => $task->id,
            'status'      => 'pabeigts',
            'done_amount' => 5,
        ]);
    }

    public function test_update_requires_spent_time_when_status_is_partial_or_finished(): void
    {
        $user = $this->login();

        $order = Order::factory()->create([
            'daudzums' => 10,
        ]);

        $production = Production::factory()->create([
            'order_id' => $order->id,
        ]);

        $process = Process::factory()->create();

        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id,
            'user_id'       => $user->id,
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ]);

        // No spent_time when marking partial -> should fail validation
        $response = $this->from(route('tasks.index'))
            ->put(route('tasks.update', $task), [
                'status'      => 'daļēji pabeigts',
                'done_amount' => 2,
                // 'spent_time' missing
            ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasErrors(['spent_time']);
    }

    public function test_update_denies_access_for_unrelated_user(): void
    {
        $user      = $this->login();
        $intruder  = User::factory()->create();
        $order     = Order::factory()->create(['daudzums' => 10]);
        $production = Production::factory()->create(['order_id' => $order->id]);
        $process   = Process::factory()->create();

        // Task belongs personally to $user
        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id,
            'user_id'       => $user->id,
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ]);

        // Act as intruder
        $this->actingAs($intruder);

        $response = $this->put(route('tasks.update', $task), [
            'status'      => 'daļēji pabeigts',
            'done_amount' => 1,
            'spent_time'  => 0.5,
        ]);

        $response->assertStatus(403);
    }

    public function test_update_allows_shared_task_for_assigned_user(): void
    {
        $user = $this->login();

        $order = Order::factory()->create([
            'daudzums' => 8,
        ]);

        $production = Production::factory()->create([
            'order_id' => $order->id,
        ]);

        $process = Process::factory()->create();

        // Shared task (user_id null)
        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id,
            'user_id'       => null,
            'status'        => 'nav uzsākts',
            'done_amount'   => 0,
        ]);

        // authorizeTask checks $task->process->users
        $process->users()->attach($user->id);

        $response = $this->put(route('tasks.update', $task), [
            'status'      => 'daļēji pabeigts',
            'done_amount' => 3,
            'spent_time'  => 1.25,
            'comment'     => 'Shared task progress',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'id'          => $task->id,
            'status'      => 'daļēji pabeigts',
            'done_amount' => 3,
        ]);
    }

    public function test_update_resets_task_when_status_is_not_started(): void
    {
        $user = $this->login();

        $order = Order::factory()->create([
            'daudzums' => 10,
        ]);

        $production = Production::factory()->create([
            'order_id' => $order->id,
        ]);

        $process = Process::factory()->create();

        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id,
            'user_id'       => $user->id,
            'status'        => 'daļēji pabeigts',
            'done_amount'   => 5,
        ]);

        $response = $this->put(route('tasks.update', $task), [
            'status'      => 'nav uzsākts',
            'done_amount' => 0,
        ]);

        $response->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', [
            'id'          => $task->id,
            'status'      => 'nav uzsākts',
            'done_amount' => 0,
        ]);
    }
}
