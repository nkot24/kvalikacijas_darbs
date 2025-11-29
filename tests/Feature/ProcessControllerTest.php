<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function login(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_index_displays_processes_with_users(): void
    {
        $this->login();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $process1 = Process::factory()->create(['processa_nosaukums' => 'Griešana']);
        $process2 = Process::factory()->create(['processa_nosaukums' => 'Metināšana']);

        $process1->users()->attach($user1->id);
        $process2->users()->attach([$user1->id, $user2->id]);

        $response = $this->get(route('processes.index'));

        $response->assertStatus(200);
        $response->assertViewIs('processes.index');
        $response->assertViewHas('processes');

        $processes = $response->viewData('processes');

        $this->assertTrue($processes->contains('id', $process1->id));
        $this->assertTrue($processes->contains('id', $process2->id));
    }

    public function test_create_displays_create_view_with_users(): void
    {
        $this->login();

        User::factory()->count(3)->create();

        $response = $this->get(route('processes.create'));

        $response->assertStatus(200);
        $response->assertViewIs('processes.create');
        $response->assertViewHas('users');
    }

    public function test_store_creates_process_and_attaches_users(): void
    {
        $this->login();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $payload = [
            'processa_nosaukums' => 'Pulēšana',
            'user_ids'           => [$user1->id, $user2->id],
        ];

        $response = $this->post(route('processes.store'), $payload);

        $response->assertRedirect(route('processes.index'));
        $response->assertSessionHas('success', 'Process created successfully.');

        $this->assertDatabaseHas('processes', [
            'processa_nosaukums' => 'Pulēšana',
        ]);

        $process = Process::where('processa_nosaukums', 'Pulēšana')->firstOrFail();

        $this->assertTrue($process->users->contains('id', $user1->id));
        $this->assertTrue($process->users->contains('id', $user2->id));
    }

    public function test_store_validates_required_name(): void
    {
        $this->login();

        $response = $this->post(route('processes.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['processa_nosaukums']);
    }

    public function test_show_displays_single_process_with_relations(): void
    {
        $this->login();

        $process = Process::factory()->create(['processa_nosaukums' => 'Cinkošana']);

        $response = $this->get(route('processes.show', $process));

        $response->assertStatus(200);
        $response->assertViewIs('processes.show');
        $response->assertViewHas('process');
    }

    public function test_edit_displays_edit_view_with_selected_users(): void
    {
        $this->login();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $process = Process::factory()->create(['processa_nosaukums' => 'Lakošana']);
        $process->users()->attach([$user1->id, $user3->id]);

        $response = $this->get(route('processes.edit', $process));

        $response->assertStatus(200);
        $response->assertViewIs('processes.edit');
        $response->assertViewHasAll(['process', 'users', 'selectedUsers']);

        $selectedUsers = $response->viewData('selectedUsers');

        $this->assertContains($user1->id, $selectedUsers);
        $this->assertContains($user3->id, $selectedUsers);
        $this->assertNotContains($user2->id, $selectedUsers);
    }

    public function test_update_updates_name_and_syncs_users_without_swap(): void
    {
        $this->login();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $process = Process::factory()->create(['processa_nosaukums' => 'Vecais nosaukums']);
        $process->users()->attach($user1->id);

        $payload = [
            'processa_nosaukums' => 'Jauns nosaukums',
            'user_ids'           => [$user2->id, $user3->id],
        ];

        $response = $this->put(route('processes.update', $process), $payload);

        $response->assertRedirect(route('processes.index'));
        $response->assertSessionHas('success', 'Process updated successfully.');

        $this->assertDatabaseHas('processes', [
            'id'                 => $process->id,
            'processa_nosaukums' => 'Jauns nosaukums',
        ]);

        $process->refresh();
        $userIds = $process->users->pluck('id')->all();

        $this->assertFalse(in_array($user1->id, $userIds, true));
        $this->assertTrue(in_array($user2->id, $userIds, true));
        $this->assertTrue(in_array($user3->id, $userIds, true));
    }

    public function test_update_validates_required_name(): void
    {
        $this->login();

        $process = Process::factory()->create(['processa_nosaukums' => 'Nosaukums']);

        $response = $this->put(route('processes.update', $process), [
            'processa_nosaukums' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['processa_nosaukums']);
    }

    public function test_destroy_deletes_process(): void
    {
        $this->login();

        $process = Process::factory()->create(['processa_nosaukums' => 'Dzēšamais process']);

        $response = $this->delete(route('processes.destroy', $process));

        $response->assertRedirect(route('processes.index'));
        $response->assertSessionHas('success', 'Process deleted successfully.');

        $this->assertDatabaseMissing('processes', [
            'id' => $process->id,
        ]);
    }
}
