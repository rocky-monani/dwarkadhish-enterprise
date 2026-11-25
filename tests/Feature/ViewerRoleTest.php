<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_view_complaint_list_and_details(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'VIEW12345',
            'complaint_number' => 'V-1001',
            'case_number' => 'CRM-VIEW-1',
            'complaint_date' => now()->format('Y-m-d'),
            'customer_name' => 'John Viewer',
            'customer_mobile' => '9876543210',
            'location' => 'Jamnagar',
            'product_model' => 'APBTZ4A',
            'complaint_status' => 'Open',
        ]);

        $this->actingAs($viewer)
            ->get(route('complaints.index'))
            ->assertStatus(200)
            ->assertSee('V-1001')
            ->assertSee('VIEW12345')
            ->assertDontSee('➕ New Complaint Entry');

        $this->actingAs($viewer)
            ->get(route('complaints.show', $complaint->id))
            ->assertStatus(200)
            ->assertSee('JOHN VIEWER')
            ->assertSee('JAMNAGAR')
            ->assertDontSee('Edit Record');
    }

    public function test_viewer_cannot_access_create_or_edit_pages(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'VIEW54321',
            'complaint_number' => 'V-1002',
            'complaint_date' => now()->format('Y-m-d'),
            'customer_name' => 'Jane Viewer',
            'complaint_status' => 'Open',
        ]);

        $this->actingAs($viewer)
            ->get(route('complaints.create'))
            ->assertRedirect()
            ->assertSessionHas('error', 'Unauthorized action. Viewer role has read-only access.');

        $this->actingAs($viewer)
            ->get(route('complaints.edit', $complaint->id))
            ->assertRedirect()
            ->assertSessionHas('error', 'Unauthorized action. Viewer role has read-only access.');
    }

    public function test_viewer_cannot_store_update_or_delete_complaint(): void
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'VIEW99999',
            'complaint_number' => 'V-1003',
            'complaint_date' => now()->format('Y-m-d'),
            'customer_name' => 'Block Test',
            'complaint_status' => 'Open',
        ]);

        $this->actingAs($viewer)
            ->post(route('complaints.store'), [
                'battery_type' => 'TW',
                'battery_serial_number' => 'NEWSERIAL',
                'complaint_number' => 'V-1004',
                'complaint_date' => now()->format('Y-m-d'),
                'customer_name' => 'New Customer',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Unauthorized action. Viewer role has read-only access.');

        $this->actingAs($viewer)
            ->put(route('complaints.update', $complaint->id), [
                'battery_type' => 'FW',
                'battery_serial_number' => 'VIEW99999',
                'complaint_number' => 'V-1003',
                'complaint_date' => now()->format('Y-m-d'),
                'customer_name' => 'Updated Name',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Unauthorized action. Viewer role has read-only access.');

        $this->actingAs($viewer)
            ->delete(route('complaints.destroy', $complaint->id))
            ->assertRedirect()
            ->assertSessionHas('error', 'Unauthorized action. Viewer role has read-only access.');
    }
}
