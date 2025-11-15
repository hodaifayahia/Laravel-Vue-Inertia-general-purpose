<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Child;
use App\Models\Appointment;
use App\Models\ProviderProfile;
use App\Models\Specialization;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create necessary roles and permissions
        $this->createPermissionsAndRoles();
    }

    protected function createPermissionsAndRoles()
    {
        // Create permissions
        $canBook = \Spatie\Permission\Models\Permission::create(['name' => 'can-book']);
        $manageBookings = \Spatie\Permission\Models\Permission::create(['name' => 'manage bookings']);
        $bookSys = \Spatie\Permission\Models\Permission::create(['name' => 'book-sys']);

        // Create roles
        $patientRole = \Spatie\Permission\Models\Role::create(['name' => 'patient']);
        $providerRole = \Spatie\Permission\Models\Role::create(['name' => 'provider']);
        $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);

        // Assign permissions to roles
        $patientRole->givePermissionTo($canBook);
        $providerRole->givePermissionTo($bookSys);
        $adminRole->givePermissionTo($manageBookings);
    }

    /**
     * Test booking appointment without child
     */
    public function test_book_appointment_without_child()
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $user->givePermissionTo('can-book');

        $city = City::factory()->create();
        $specialization = Specialization::factory()->create();
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $provider->givePermissionTo('book-sys');

        $providerProfile = ProviderProfile::factory()->create([
            'user_id' => $provider->id,
            'city_id' => $city->id,
            'specialization_id' => $specialization->id,
            'is_available' => true,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/appointments', [
            'provider_profile_id' => $providerProfile->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'notes' => 'Test appointment',
            'child_id' => null,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'message', 'appointment']);
        
        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'provider_profile_id' => $providerProfile->id,
            'child_id' => null,
            'status' => 'pending',
        ]);
    }

    /**
     * Test booking appointment with child (THE MAIN TEST)
     */
    public function test_book_appointment_with_child()
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $user->givePermissionTo('can-book');

        // Create a child for this user
        $child = Child::factory()->create([
            'partner_id' => $user->id,
            'name' => 'Ahmed',
            'date_of_birth' => now()->subYears(5)->toDateString(),
        ]);

        $city = City::factory()->create();
        $specialization = Specialization::factory()->create();
        $provider = User::factory()->create();
        $provider->assignRole('provider');
        $provider->givePermissionTo('book-sys');

        $providerProfile = ProviderProfile::factory()->create([
            'user_id' => $provider->id,
            'city_id' => $city->id,
            'specialization_id' => $specialization->id,
            'is_available' => true,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/appointments', [
            'provider_profile_id' => $providerProfile->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'notes' => 'Appointment for child',
            'child_id' => $child->id,
        ]);

        \Log::info('Response status: ' . $response->status());
        \Log::info('Response body: ' . json_encode($response->json()));

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'message', 'appointment']);
        
        // CRITICAL TEST: Verify child_id is saved
        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'provider_profile_id' => $providerProfile->id,
            'child_id' => $child->id,
            'status' => 'pending',
        ]);

        // Verify the child relationship
        $appointment = Appointment::where('child_id', $child->id)->first();
        $this->assertNotNull($appointment);
        $this->assertEquals($child->id, $appointment->child_id);
        $this->assertEquals('Ahmed', $appointment->child->name);
    }

    /**
     * Test invalid child_id is rejected
     */
    public function test_booking_with_invalid_child_id_fails()
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $user->givePermissionTo('can-book');

        $city = City::factory()->create();
        $specialization = Specialization::factory()->create();
        $provider = User::factory()->create();
        $provider->assignRole('provider');

        $providerProfile = ProviderProfile::factory()->create([
            'user_id' => $provider->id,
            'city_id' => $city->id,
            'specialization_id' => $specialization->id,
            'is_available' => true,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/appointments', [
            'provider_profile_id' => $providerProfile->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'notes' => 'Invalid child test',
            'child_id' => 99999, // Non-existent child
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test appointment with child appears in appointments list
     */
    public function test_child_appointment_shows_in_list()
    {
        $user = User::factory()->create();
        $user->assignRole('patient');
        $user->givePermissionTo('can-book');

        $child = Child::factory()->create([
            'partner_id' => $user->id,
        ]);

        $city = City::factory()->create();
        $specialization = Specialization::factory()->create();
        $provider = User::factory()->create();
        $providerProfile = ProviderProfile::factory()->create([
            'user_id' => $provider->id,
            'city_id' => $city->id,
            'specialization_id' => $specialization->id,
        ]);

        $appointment = Appointment::factory()->create([
            'user_id' => $user->id,
            'child_id' => $child->id,
            'provider_profile_id' => $providerProfile->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user);

        $response = $this->get('/appointments');
        $response->assertStatus(200);

        // Verify appointment appears in the list
        $response->assertSee($child->name);
    }
}
