<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_an_avatar_stores_it_on_the_user(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '9876543210',
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertNotNull($user->image_path);
        Storage::disk('public')->assertExists($user->image_path);
    }

    public function test_resubmitting_the_profile_without_a_new_file_keeps_the_existing_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->forceFill(['image_path' => 'user_image/existing.jpg'])->save();
        Storage::disk('public')->put('user_image/existing.jpg', 'fake-contents');

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '9876543210',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('user_image/existing.jpg', $user->refresh()->image_path);
        Storage::disk('public')->assertExists('user_image/existing.jpg');
    }

    public function test_remove_avatar_clears_the_existing_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->forceFill(['image_path' => 'user_image/existing.jpg'])->save();
        Storage::disk('public')->put('user_image/existing.jpg', 'fake-contents');

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '9876543210',
            'remove_avatar' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertNull($user->refresh()->image_path);
        Storage::disk('public')->assertMissing('user_image/existing.jpg');
    }
}
