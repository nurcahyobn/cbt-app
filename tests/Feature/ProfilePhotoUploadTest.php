<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'foto' => $file,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->foto);
        Storage::disk('public')->assertExists($user->foto);
    }

    public function test_old_photo_is_deleted_when_new_photo_is_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // 1. Upload first photo
        $file1 = UploadedFile::fake()->image('first.jpg');
        $this->actingAs($user)->patch('/profile', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'foto' => $file1,
        ]);

        $user->refresh();
        $firstPhoto = $user->foto;
        Storage::disk('public')->assertExists($firstPhoto);

        // 2. Upload second photo
        $file2 = UploadedFile::fake()->image('second.png');
        $this->actingAs($user)->patch('/profile', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'foto' => $file2,
        ]);

        $user->refresh();
        $secondPhoto = $user->foto;

        $this->assertNotSame($firstPhoto, $secondPhoto);
        Storage::disk('public')->assertMissing($firstPhoto);
        Storage::disk('public')->assertExists($secondPhoto);
    }

    public function test_user_can_delete_only_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($user)->patch('/profile', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'foto' => $file,
        ]);

        $user->refresh();
        $photoPath = $user->foto;
        Storage::disk('public')->assertExists($photoPath);

        // Delete photo via dedicated endpoint
        $response = $this->actingAs($user)->delete('/profile/foto');

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $user->refresh();
        $this->assertNull($user->foto);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_alias_profil_routes_work(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // GET /profil
        $response = $this->actingAs($user)->get('/profil');
        $response->assertOk();

        // PUT /profil with photo
        $file = UploadedFile::fake()->image('avatar.webp');
        $updateResponse = $this->actingAs($user)->put('/profil', [
            'name' => 'Siswa CBT',
            'email' => 'siswa@cbt.id',
            'foto' => $file,
        ]);

        $updateResponse->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNotNull($user->foto);
        Storage::disk('public')->assertExists($user->foto);

        // DELETE /profil/foto
        $delResponse = $this->actingAs($user)->delete('/profil/foto');
        $delResponse->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertNull($user->foto);
    }

    public function test_photo_validation_fails_for_non_image_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $nonImageFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'foto' => $nonImageFile,
            ]);

        $response->assertSessionHasErrors(['foto']);

        $user->refresh();
        $this->assertNull($user->foto);
    }

    public function test_photo_validation_fails_for_files_exceeding_max_size(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        // File 3MB (melebihi batas 2048 KB)
        $largeFile = UploadedFile::fake()->create('huge_avatar.jpg', 3072, 'image/jpeg');

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
                'foto' => $largeFile,
            ]);

        $response->assertSessionHasErrors(['foto']);

        $user->refresh();
        $this->assertNull($user->foto);
    }

    public function test_photo_is_deleted_from_storage_when_user_account_is_deleted(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($user)->patch('/profile', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'foto' => $file,
        ]);

        $user->refresh();
        $photoPath = $user->foto;
        Storage::disk('public')->assertExists($photoPath);

        // Delete account
        $response = $this->actingAs($user)->delete('/profile', [
            'password' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNull(User::find($user->id));
        Storage::disk('public')->assertMissing($photoPath);
    }
}
