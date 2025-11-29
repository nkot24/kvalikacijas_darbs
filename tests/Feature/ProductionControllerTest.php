<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Process;
use App\Models\ProcessFile;
use App\Models\Production;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function login(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_create_displays_create_view_with_orders_processes_and_users(): void
    {
        $this->login();

        Order::factory()->create(['statuss' => 'nav nodots ražošanai']);
        Process::factory()->count(2)->create();
        User::factory()->count(3)->create();

        $response = $this->get(route('productions.create'));

        $response->assertStatus(200);
        $response->assertViewIs('productions.create');
        $response->assertViewHasAll(['orders', 'processes', 'users']);
    }

    public function test_store_creates_production_tasks_updates_order_and_redirects(): void
    {
        $this->login();

        $order    = Order::factory()->create(['statuss' => 'nav nodots ražošanai']);
        $process1 = Process::factory()->create();
        $process2 = Process::factory()->create();

        $worker1 = User::factory()->create();
        $worker2 = User::factory()->create();

        $payload = [
            'order_id'    => $order->id,
            'process_ids' => [$process1->id, $process2->id],
            'users'       => [
                $process1->id => [$worker1->id],
                $process2->id => [$worker2->id],
            ],
        ];

        $response = $this->post(route('productions.store'), $payload);

        $response->assertRedirect(route('orders.show', $order->id));

        $production = Production::where('order_id', $order->id)->first();

        $this->assertNotNull($production);

        // Tasks created
        $this->assertDatabaseHas('tasks', [
            'production_id' => $production->id,
            'process_id'    => $process1->id
        ]);

        $this->assertDatabaseHas('tasks', [
            'production_id' => $production->id,
            'process_id'    => $process2->id
        ]);

        // Order updated
        $this->assertDatabaseHas('orders', [
            'id'      => $order->id,
            'statuss' => 'nodots ražošanai'
        ]);
    }

    public function test_store_handles_process_and_global_files(): void
    {
        $this->login();
        Storage::fake('public');

        $order    = Order::factory()->create(['statuss' => 'nav nodots ražošanai']);
        $process1 = Process::factory()->create();
        $process2 = Process::factory()->create();

        $payload = [
            'order_id'    => $order->id,
            'process_ids' => [$process1->id, $process2->id],
            'process_files' => [
                $process1->id => [
                    UploadedFile::fake()->create('p1-file1.pdf', 10)
                ]
            ],
            'global_files' => [
                UploadedFile::fake()->create('global-spec.pdf', 20)
            ]
        ];

        $response = $this->post(route('productions.store'), $payload);
        $response->assertRedirect(route('orders.show', $order->id));

        $production = Production::first();
        $tasks      = Task::where('production_id', $production->id)->get();

        $this->assertCount(2, $tasks);

        // Process-specific file
        $this->assertDatabaseHas('process_files', [
            'original_name' => 'p1-file1.pdf'
        ]);

        // Global files (attached to both tasks)
        $this->assertEquals(
            2,
            ProcessFile::where('original_name', 'global-spec.pdf')->count()
        );
    }

    public function test_edit_displays_edit_view_with_correct_data(): void
    {
        $this->login();

        $order    = Order::factory()->create();
        $process1 = Process::factory()->create();
        $process2 = Process::factory()->create();

        $production = Production::factory()->create(['order_id' => $order->id]);

        Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process1->id
        ]);

        Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process2->id
        ]);

        $response = $this->get(route('productions.edit', $production));

        $response->assertStatus(200);
        $response->assertViewIs('productions.edit');
        $response->assertViewHasAll([
            'production',
            'orders',
            'processes',
            'selectedProcessIds',
            'selectedUsersByProcess',
        ]);
    }

    public function test_update_changes_processes_and_keeps_existing_task_progress(): void
    {
        $this->login();

        $order    = Order::factory()->create();
        $p1 = Process::factory()->create();
        $p2 = Process::factory()->create();
        $p3 = Process::factory()->create();

        $production = Production::factory()->create(['order_id' => $order->id]);

        $task1 = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $p1->id,
            'status'        => 'procesā',
            'done_amount'   => 7
        ]);

        $task2 = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $p2->id
        ]);

        $payload = [
            'order_id'    => $order->id,
            'process_ids' => [$p1->id, $p3->id],
        ];

        $response = $this->put(route('productions.update', $production), $payload);

        $response->assertRedirect(route('orders.show', $order->id));

        // Task1 kept
        $this->assertDatabaseHas('tasks', [
            'id'          => $task1->id,
            'process_id'  => $p1->id,
            'done_amount' => 7,
            'status'      => 'procesā'
        ]);

        // Task2 removed
        $this->assertDatabaseMissing('tasks', [
            'id' => $task2->id
        ]);

        // Task3 created
        $this->assertDatabaseHas('tasks', [
            'production_id' => $production->id,
            'process_id'    => $p3->id
        ]);
    }

    public function test_destroy_deletes_production_and_files_directory(): void
    {
        $this->login();
        Storage::fake('public');

        $order      = Order::factory()->create();
        $process    = Process::factory()->create();
        $production = Production::factory()->create(['order_id' => $order->id]);

        $task = Task::factory()->create([
            'production_id' => $production->id,
            'process_id'    => $process->id
        ]);

        $storedPath = "process_files/production_{$production->id}/process_{$process->id}/file.pdf";
        Storage::disk('public')->put($storedPath, 'dummy-data');

        ProcessFile::create([
            'process_id'    => $process->id,
            'task_id'       => $task->id,
            'uploaded_by'   => User::factory()->create()->id,
            'original_name' => 'file.pdf',
            'path'          => $storedPath,
            'mime'          => 'application/pdf',
            'size'          => 1234,
        ]);

        $response = $this->delete(route('productions.destroy', $production));
        $response->assertRedirect(route('productions.index'));

        $this->assertDatabaseMissing('productions', [
            'id' => $production->id
        ]);

        Storage::disk('public')->assertMissing("process_files/production_{$production->id}");
    }

    public function test_store_validates_required_fields(): void
    {
        $this->login();

        $response = $this->post(route('productions.store'), []);

        $response->assertSessionHasErrors(['order_id', 'process_ids']);
    }
}
