<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MaterialScan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaterialScanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function login(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_scan_view_returns_view_for_authenticated_user(): void
    {
        $this->login();

        $response = $this->get(route('inventory.materials.scan'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.materials-scan');
    }

    public function test_store_scan_creates_material_scan_and_returns_json(): void
    {
        $user = $this->login();

        $payload = [
            'svitr_kods' => ' 1234567890 ', // to test trim()
            'qty'        => 5,
        ];

        $response = $this->postJson(route('inventory.materials.store'), $payload);

        $response
            ->assertStatus(200)
            ->assertJson([
                'ok'      => true,
                'message' => 'Materiāls saglabāts.',
            ])
            ->assertJsonStructure([
                'ok',
                'message',
                'scan' => [
                    'id',
                    'svitr_kods',
                    'qty',
                    'created_by',
                ],
            ]);

        $this->assertDatabaseHas('material_scans', [
            'svitr_kods' => '1234567890', // trimmed
            'qty'        => 5,
            'created_by' => $user->id,
        ]);
    }

    public function test_store_scan_validates_required_fields(): void
    {
        $this->login();

        // Non-JSON request => should redirect with errors (302)
        $response = $this->post(route('inventory.materials.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['svitr_kods', 'qty']);
    }

    public function test_index_lists_scans_and_can_filter_by_q_and_only_not_accounted(): void
    {
        $this->login();

        $scan1 = MaterialScan::factory()->create([
            'svitr_kods' => 'ABC123',
            'accounted'  => false,
        ]);

        $scan2 = MaterialScan::factory()->create([
            'svitr_kods' => 'DEF456',
            'accounted'  => true,
        ]);

        $scan3 = MaterialScan::factory()->create([
            'svitr_kods' => 'ABC999',
            'accounted'  => false,
        ]);

        // No filters
        $response = $this->get(route('inventory.materials.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.materials-list');
        $response->assertViewHas('scans', function ($scans) use ($scan1, $scan2, $scan3) {
            return $scans->contains($scan1)
                && $scans->contains($scan2)
                && $scans->contains($scan3);
        });

        // Filter by q = "ABC"
        $response = $this->get(route('inventory.materials.index', ['q' => 'ABC']));

        $response->assertStatus(200);
        $response->assertViewHas('scans', function ($scans) use ($scan1, $scan2, $scan3) {
            return $scans->contains($scan1)
                && $scans->contains($scan3)
                && ! $scans->contains($scan2);
        });

        // Filter only not accounted
        $response = $this->get(route('inventory.materials.index', ['only_not_accounted' => 1]));

        $response->assertStatus(200);
        $response->assertViewHas('scans', function ($scans) use ($scan1, $scan2, $scan3) {
            return $scans->contains($scan1)
                && $scans->contains($scan3)
                && ! $scans->contains($scan2);
        });
    }

    public function test_bulk_account_sets_accounted_true_by_default_and_sets_timestamp(): void
    {
        $this->login();

        $scans = MaterialScan::factory()->count(3)->create([
            'accounted'    => false,
            'accounted_at' => null,
        ]);

        $ids = $scans->pluck('id')->toArray();

        $response = $this->patchJson(route('inventory.materials.account'), [
            'ids' => $ids,
            // no 'accounted' => defaults to true in controller
        ]);

        $response->assertStatus(200)
                 ->assertJson(['ok' => true]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('material_scans', [
                'id'        => $id,
                'accounted' => true,
            ]);

            $this->assertNotNull(MaterialScan::find($id)->accounted_at);
        }
    }

    public function test_bulk_account_can_set_accounted_false_and_nullify_timestamp(): void
    {
        $this->login();

        $scans = MaterialScan::factory()->count(2)->create([
            'accounted'    => true,
            'accounted_at' => now(),
        ]);

        $ids = $scans->pluck('id')->toArray();

        $response = $this->patchJson(route('inventory.materials.account'), [
            'ids'       => $ids,
            'accounted' => false,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['ok' => true]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('material_scans', [
                'id'           => $id,
                'accounted'    => false,
                'accounted_at' => null,
            ]);
        }
    }

    public function test_bulk_account_validates_ids_array(): void
    {
        $this->login();

        $response = $this->patchJson(route('inventory.materials.account'), [
            'ids' => 'not-an-array',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ids']);
    }

    public function test_bulk_delete_deletes_selected_records(): void
    {
        $this->login();

        $scansToDelete = MaterialScan::factory()->count(2)->create();
        $scansToKeep   = MaterialScan::factory()->count(2)->create();

        $idsToDelete = $scansToDelete->pluck('id')->toArray();

        $response = $this->deleteJson(route('inventory.materials.delete'), [
            'ids' => $idsToDelete,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'ok'      => true,
                'message' => 'Ieraksti dzēsti.',
            ]);

        foreach ($idsToDelete as $id) {
            $this->assertDatabaseMissing('material_scans', ['id' => $id]);
        }

        foreach ($scansToKeep as $scan) {
            $this->assertDatabaseHas('material_scans', ['id' => $scan->id]);
        }
    }

    public function test_bulk_delete_validates_ids_array(): void
    {
        $this->login();

        $response = $this->deleteJson(route('inventory.materials.delete'), [
            'ids' => 'not-an-array',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ids']);
    }
}
