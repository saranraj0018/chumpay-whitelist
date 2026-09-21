<?php

namespace Tests\Unit;

use App\Http\Controllers\api\AddressController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AddressControllerTest extends TestCase
{
    private AddressController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestSchema();

        $this->controller = new AddressController();
    }

    protected function tearDown(): void
    {
        Auth::logout();
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_index_returns_only_addresses_created_by_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Auth::login($user);

        $ownAddressId = $this->createAddress($user, ['name' => 'Home Address']);
        $this->createAddress($otherUser, ['name' => 'Other User Address']);

        $response = $this->controller->index();
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $payload['status']);
        $this->assertSame('All addresses retrieved successfully', $payload['message']);
        $this->assertCount(1, $payload['data']);
        $this->assertSame($ownAddressId, $payload['data'][0]['id']);
        $this->assertSame('Home Address', $payload['data'][0]['name']);
    }

    public function test_index_returns_empty_array_when_user_has_no_addresses(): void
    {
        Auth::login(User::factory()->create());

        $response = $this->controller->index();
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $payload['status']);
        $this->assertSame([], $payload['data']);
        $this->assertSame('Address Not Found', $payload['message']);
    }

    public function test_save_creates_address_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $response = $this->controller->save(new Request($this->validPayload([
            'is_default' => true,
            'latitude' => 6.9271,
            'longitude' => 79.8612,
            'landmark' => 'Main gate',
        ])));
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $payload['status']);
        $this->assertSame('Address created successfully', $payload['message']);
        $this->assertDatabaseHas('addresses', [
            'name' => 'Test User',
            'phone_number' => '0771234567',
            'address' => '123 Main Street',
            'pincode' => '10000',
            'state' => 'Western',
            'city' => 'Colombo',
            'address_type' => 'home',
            'is_default' => true,
            'latitude' => 6.9271,
            'longitude' => 79.8612,
            'landmark' => 'Main gate',
            'created_by' => $user->id,
        ]);
    }

    public function test_save_returns_validation_error_when_required_data_is_missing(): void
    {
        Auth::login(User::factory()->create());

        $response = $this->controller->save(new Request([]));
        $payload = $response->getData(true);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(409, $payload['status']);
        $this->assertSame('The name field is required.', $payload['message']);
        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_update_changes_address_and_clears_other_default_addresses_for_user(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $oldDefaultId = $this->createAddress($user, [
            'name' => 'Old Default',
            'is_default' => true,
        ]);
        $addressId = $this->createAddress($user, [
            'name' => 'Work Address',
            'is_default' => false,
        ]);

        $response = $this->controller->update(new Request($this->validPayload([
            'id' => $addressId,
            'name' => 'Updated Work',
            'city' => 'Kandy',
            'is_default' => true,
            'latitude' => 7.2906,
            'longitude' => 80.6337,
        ])));
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $payload['status']);
        $this->assertSame('Address updated successfully', $payload['message']);
        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'name' => 'Updated Work',
            'city' => 'Kandy',
            'is_default' => true,
            'latitude' => 7.2906,
            'longitude' => 80.6337,
            'updated_by' => $user->id,
        ]);
        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefaultId,
            'is_default' => false,
        ]);
    }

    public function test_set_default_address_rejects_address_owned_by_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Auth::login($user);

        $otherAddressId = $this->createAddress($otherUser);

        $response = $this->controller->setDefaultAddress(new Request([
            'address_id' => $otherAddressId,
        ]));
        $payload = $response->getData(true);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(404, $payload['status']);
        $this->assertSame('Address not found or unauthorized.', $payload['message']);
    }

    public function test_set_default_address_clears_existing_default_and_sets_selected_address(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $oldDefaultId = $this->createAddress($user, ['is_default' => true]);
        $newDefaultId = $this->createAddress($user, ['is_default' => false]);

        $response = $this->controller->setDefaultAddress(new Request([
            'address_id' => $newDefaultId,
        ]));
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $payload['status']);
        $this->assertSame('Address set as default successfully', $payload['message']);
        $this->assertDatabaseHas('addresses', [
            'id' => $oldDefaultId,
            'is_default' => false,
        ]);
        $this->assertDatabaseHas('addresses', [
            'id' => $newDefaultId,
            'is_default' => true,
        ]);
    }

    public function test_delete_removes_address(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $addressId = $this->createAddress($user);

        $response = $this->controller->delete(new Request([
            'address_id' => $addressId,
        ]));
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(200, $payload['status']);
        $this->assertSame('Address deleted successfully', $payload['message']);
        $this->assertDatabaseMissing('addresses', [
            'id' => $addressId,
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'phone_number' => '0771234567',
            'address' => '123 Main Street',
            'pincode' => '10000',
            'state' => 'Western',
            'city' => 'Colombo',
            'address_type' => 'home',
        ], $overrides);
    }

    private function createAddress(User $user, array $overrides = []): int
    {
        return DB::table('addresses')->insertGetId(array_merge([
            'name' => 'Test User',
            'phone_number' => '0771234567',
            'address' => '123 Main Street',
            'pincode' => '10000',
            'state' => 'Western',
            'city' => 'Colombo',
            'address_type' => 'home',
            'is_default' => false,
            'latitude' => null,
            'longitude' => null,
            'landmark' => null,
            'created_by' => $user->id,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    private function createTestSchema(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('addresses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('phone_number');
            $table->string('address');
            $table->string('pincode');
            $table->string('state');
            $table->string('city');
            $table->string('address_type');
            $table->boolean('is_default')->default(false);
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
            $table->text('landmark')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('no action');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('no action');
            $table->timestamps();
        });
    }
}
