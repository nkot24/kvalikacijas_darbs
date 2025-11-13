<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Models\DeliveryAddress;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ClientsFullExport;
use App\Imports\ClientsFullImport;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Authenticate a user before each test
        $this->actingAs(User::factory()->create());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_displays_the_clients_index()
    {
        $client = Client::factory()->create(['nosaukums' => 'Test Client']);

        $response = $this->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertSeeText('Test Client');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_store_a_client_with_contacts_and_addresses()
    {
        $data = [
            'nosaukums' => 'Test Client',
            'registracijas_numurs' => '12345678',
            'pvn_maksataja_numurs' => 'LV123456789',
            'juridiska_adrese' => 'Client Address',
            'contact_persons' => [
                ['kontakt_personas_vards' => 'John Doe', 'e-pasts' => 'john@example.com', 'telefons' => '12345678'],
            ],
            'delivery_addresses' => [
                ['piegades_adrese' => 'Delivery Address 1'],
            ],
        ];

        $response = $this->post(route('clients.store'), $data);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', ['nosaukums' => 'Test Client']);
        $this->assertDatabaseHas('contact_persons', ['kontakt_personas_vards' => 'John Doe']);
        $this->assertDatabaseHas('delivery_addresses', ['piegades_adrese' => 'Delivery Address 1']);
    }



    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_a_client_with_contacts_and_addresses()
    {
        $client = Client::factory()->create();
        $contact = ContactPerson::factory()->create(['client_id' => $client->id]);
        $address = DeliveryAddress::factory()->create(['client_id' => $client->id]);

        $data = [
            'nosaukums' => 'Updated Client',
            'registracijas_numurs' => '87654321',
            'pvn_maksataja_numurs' => 'LV987654321',
            'juridiska_adrese' => 'Updated Address',
            'contact_persons' => [
                ['id' => $contact->id, 'kontakt_personas_vards' => 'Jane Doe', 'e-pasts' => 'jane@example.com', 'telefons' => '87654321'],
            ],
            'delivery_addresses' => [
                ['id' => $address->id, 'piegades_adrese' => 'Updated Delivery Address'],
            ],
        ];

        $response = $this->put(route('clients.update', $client), $data);

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseHas('clients', ['nosaukums' => 'Updated Client']);
        $this->assertDatabaseHas('contact_persons', ['kontakt_personas_vards' => 'Jane Doe']);
        $this->assertDatabaseHas('delivery_addresses', ['piegades_adrese' => 'Updated Delivery Address']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_a_client()
    {
        $client = Client::factory()->create();

        $response = $this->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_export_clients_to_excel()
    {
        Excel::fake();

        // Trigger the route that should return a download
        $response = $this->get(route('clients.fullExport'));

        $response->assertStatus(200);

        // Make sure the export was "downloaded"
        Excel::assertDownloaded('clients.xlsx', function ($export) {
            return $export instanceof ClientsFullExport;
        });
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_import_clients_from_excel()
    {
        Excel::fake();

        $file = UploadedFile::fake()->create('clients.xlsx');

        $response = $this->post(route('clients.fullImport'), [
            'import_file' => $file
        ]);

        $response->assertRedirect(route('clients.index'));

        Excel::assertImported('clients.xlsx', function (ClientsFullImport $import) {
            return $import instanceof ClientsFullImport;
        });
    }
}
