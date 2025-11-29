<?php

namespace Tests\Feature;

use App\Models\Process;
use App\Models\ProcessFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessFileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function login(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->actingAs($user);

        return $user;
    }

    public function test_store_uploads_files_when_user_assigned_to_process(): void
    {
        Storage::fake('public');

        $user    = $this->login();
        $process = Process::factory()->create();
        $process->users()->attach($user->id); // user is allowed

        $file1 = UploadedFile::fake()->create('doc1.pdf', 100);
        $file2 = UploadedFile::fake()->image('image1.jpg', 100, 100);

        $response = $this->from('/some-page')->post(route('process-files.store'), [
            'process_id' => $process->id,
            'files'      => [$file1, $file2],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Faili veiksmīgi augšupielādēti.');

        // Two records created
        $this->assertEquals(
            2,
            ProcessFile::where('process_id', $process->id)->count()
        );

        foreach (ProcessFile::where('process_id', $process->id)->get() as $storedFile) {
            Storage::disk('public')->assertExists($storedFile->path);
            $this->assertEquals($user->id, $storedFile->uploaded_by);
        }
    }

    public function test_store_forbidden_when_user_not_assigned_and_not_admin(): void
    {
        Storage::fake('public');

        $user    = $this->login(); // normal user
        $process = Process::factory()->create(); // no user attached

        $file = UploadedFile::fake()->create('forbidden.pdf', 100);

        $response = $this->post(route('process-files.store'), [
            'process_id' => $process->id,
            'files'      => [$file],
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('process_files', 0);
    }

    public function test_download_allows_admin_and_returns_file(): void
    {
        Storage::fake('public');

        $admin = $this->login(['role' => 'admin']);
        $process = Process::factory()->create();

        $path = "process_files/{$process->id}/test.pdf";
        Storage::disk('public')->put($path, 'dummy pdf content');

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $admin->id,
            'original_name' => 'test.pdf',
            'path'          => $path,
            'mime'          => 'application/pdf',
            'size'          => 123,
        ]);

        $response = $this->get(route('process-files.download', $file));

        $response->assertOk();
        $this->assertStringContainsString(
            'attachment; filename=test.pdf',
            $response->headers->get('content-disposition', '')
        );
    }

    public function test_download_forbidden_for_unrelated_user(): void
    {
        Storage::fake('public');

        $uploader = User::factory()->create();
        $user     = $this->login(); // logged in but unrelated
        $process  = Process::factory()->create();

        $path = "process_files/{$process->id}/secret.pdf";
        Storage::disk('public')->put($path, 'secret');

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $uploader->id,
            'original_name' => 'secret.pdf',
            'path'          => $path,
            'mime'          => 'application/pdf',
            'size'          => 50,
        ]);

        $response = $this->get(route('process-files.download', $file));

        $response->assertStatus(403);
    }

    public function test_download_returns_404_if_file_missing(): void
    {
        Storage::fake('public');

        $admin   = $this->login(['role' => 'admin']);
        $process = Process::factory()->create();

        $path = "process_files/{$process->id}/missing.pdf";

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $admin->id,
            'original_name' => 'missing.pdf',
            'path'          => $path,
            'mime'          => 'application/pdf',
            'size'          => 50,
        ]);

        // We did NOT put the file into Storage, so it should 404
        $response = $this->get(route('process-files.download', $file));

        $response->assertStatus(404);
    }

    public function test_view_shows_inline_for_pdf(): void
    {
        Storage::fake('public');

        $admin   = $this->login(['role' => 'admin']);
        $process = Process::factory()->create();

        $path = "process_files/{$process->id}/inline.pdf";
        Storage::disk('public')->put($path, 'pdf content');

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $admin->id,
            'original_name' => 'inline.pdf',
            'path'          => $path,
            'mime'          => 'application/pdf',
            'size'          => 100,
        ]);

        $response = $this->get(route('process-files.view', $file));

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_view_downloads_for_non_inline_type(): void
    {
        Storage::fake('public');

        $admin   = $this->login(['role' => 'admin']);
        $process = Process::factory()->create();

        $path = "process_files/{$process->id}/archive.zip";
        Storage::disk('public')->put($path, 'zip content');

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $admin->id,
            'original_name' => 'archive.zip',
            'path'          => $path,
            'mime'          => 'application/zip',
            'size'          => 200,
        ]);

        $response = $this->get(route('process-files.view', $file));

        $response->assertOk();
        $this->assertStringContainsString(
            'attachment; filename=archive.zip',
            $response->headers->get('content-disposition', '')
        );
    }

    public function test_destroy_deletes_file_and_record_when_authorized(): void
    {
        Storage::fake('public');

        $admin   = $this->login(['role' => 'admin']);
        $process = Process::factory()->create();

        $path = "process_files/{$process->id}/delete_me.pdf";
        Storage::disk('public')->put($path, 'to be deleted');

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $admin->id,
            'original_name' => 'delete_me.pdf',
            'path'          => $path,
            'mime'          => 'application/pdf',
            'size'          => 123,
        ]);

        $response = $this->from('/back-page')->delete(route('process-files.destroy', $file));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Fails dzēsts.');

        $this->assertDatabaseMissing('process_files', [
            'id' => $file->id,
        ]);

        Storage::disk('public')->assertMissing($path);
    }

    public function test_destroy_forbidden_for_unrelated_user(): void
    {
        Storage::fake('public');

        $uploader = User::factory()->create();
        $user     = $this->login(); // unrelated
        $process  = Process::factory()->create();

        $path = "process_files/{$process->id}/protected.pdf";
        Storage::disk('public')->put($path, 'protected');

        $file = ProcessFile::create([
            'process_id'    => $process->id,
            'uploaded_by'   => $uploader->id,
            'original_name' => 'protected.pdf',
            'path'          => $path,
            'mime'          => 'application/pdf',
            'size'          => 50,
        ]);

        $response = $this->delete(route('process-files.destroy', $file));

        $response->assertStatus(403);

        // File should still exist
        $this->assertDatabaseHas('process_files', ['id' => $file->id]);
        Storage::disk('public')->assertExists($path);
    }
}
