<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_complaints(): void
    {
        $response = $this->get('/complaints');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_complaints(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/complaints');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_next_complaint_number_increments_by_battery_type(): void
    {
        $user = User::factory()->create();

        \App\Models\BatteryComplaint::create([
            'battery_serial_number' => 'SERIALTW100',
            'complaint_number' => '1010',
            'complaint_date' => '2026-09-09',
            'customer_name' => 'TW USER',
            'battery_type' => 'TW',
        ]);

        $response = $this->actingAs($user)->get('/complaints/next-number?battery_type=TW');
        $response->assertStatus(200);
        $response->assertJson(['next_complaint_number' => '1011']);

        \App\Models\BatteryComplaint::create([
            'battery_serial_number' => 'SERIALFW200',
            'complaint_number' => '2050',
            'complaint_date' => '2026-09-09',
            'customer_name' => 'FW USER',
            'battery_type' => 'FW',
        ]);

        $responseFW = $this->actingAs($user)->get('/complaints/next-number?battery_type=FW');
        $responseFW->assertStatus(200);
        $responseFW->assertJson(['next_complaint_number' => '2051']);
    }

    public function test_can_create_complaint_with_battery_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/complaints', [
            'battery_type' => 'FW',
            'battery_serial_number' => 'FWBATTERY123',
            'product_model' => 'DIN74',
            'complaint_number' => '2051',
            'complaint_date' => '2026-09-09',
            'customer_name' => 'CAR OWNER',
            'customer_mobile' => '9998887770',
        ]);

        $response->assertRedirect(route('complaints.create'));

        $this->assertDatabaseHas('battery_complaints', [
            'battery_type' => 'FW',
            'battery_serial_number' => 'FWBATTERY123',
            'complaint_number' => '2051',
            'customer_name' => 'CAR OWNER',
        ]);
    }

    public function test_can_access_close_case_view_and_live_search(): void
    {
        $user = User::factory()->create();

        \App\Models\BatteryComplaint::create([
            'battery_serial_number' => 'SEARCHBAT123',
            'complaint_number' => '5001',
            'complaint_date' => '2026-09-10',
            'customer_name' => 'SEARCH CUSTOMER',
            'customer_mobile' => '9876543210',
            'complaint_status' => 'Open',
        ]);

        $responseView = $this->actingAs($user)->get('/complaints/close-case');
        $responseView->assertStatus(200);
        $responseView->assertSee('Close Complaint Case');

        $responseSearch = $this->actingAs($user)->get('/complaints/live-search-open?complaint_number=5001');
        $responseSearch->assertStatus(200);
        $responseSearch->assertJsonFragment(['complaint_number' => '5001']);
    }

    public function test_can_close_complaint_direct(): void
    {
        $user = User::factory()->create();

        $complaint = \App\Models\BatteryComplaint::create([
            'battery_serial_number' => 'CLOSEBAT999',
            'complaint_number' => '5002',
            'complaint_date' => '2026-09-10',
            'customer_name' => 'CLOSE TARGET',
            'complaint_status' => 'Open',
        ]);

        $responseClose = $this->actingAs($user)->postJson("/complaints/{$complaint->id}/close-direct", [
            'complaint_status' => 'Closed',
            'final_decision' => 'F',
            'decision_date' => '2026-09-10',
            'new_battery_number' => 'REPLACEMENT123',
            'closing_date' => '2026-09-10',
        ]);

        $responseClose->assertJson(['success' => true]);

        $this->assertDatabaseHas('battery_complaints', [
            'id' => $complaint->id,
            'complaint_status' => 'Closed',
            'final_decision' => 'F',
            'new_battery_number' => 'REPLACEMENT123',
        ]);
    }

    public function test_dashboard_displays_excel_import_and_export_buttons(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('IMPORT EXCEL DATA');
        $response->assertSee('EXPORT EXCEL SHEET');
    }

    public function test_operator_cannot_delete_complaint_and_admin_can(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $operator = User::factory()->create(['role' => 'operator1']);

        $complaint = \App\Models\BatteryComplaint::create([
            'battery_serial_number' => 'DELTEST123',
            'complaint_number' => '9901',
            'complaint_date' => '2026-09-10',
            'customer_name' => 'DELETE TARGET',
            'complaint_status' => 'Open',
        ]);

        // Operator attempt should fail with unauthorized redirect
        $responseOp = $this->actingAs($operator)->delete("/complaints/{$complaint->id}");
        $responseOp->assertSessionHas('error');
        $this->assertDatabaseHas('battery_complaints', ['id' => $complaint->id, 'is_deleted' => false]);

        // Admin attempt should succeed
        $responseAdmin = $this->actingAs($admin)->delete("/complaints/{$complaint->id}");
        $responseAdmin->assertRedirect(route('complaints.index'));
        $this->assertDatabaseHas('battery_complaints', ['id' => $complaint->id, 'is_deleted' => true]);
    }

    public function test_operator_complaint_list_hides_sensitive_columns(): void
    {
        $operator = User::factory()->create(['role' => 'operator1']);
        $admin = User::factory()->create(['role' => 'admin']);

        $responseOp = $this->actingAs($operator)->get('/complaints');
        $responseOp->assertStatus(200);
        $responseOp->assertDontSee('Customer Name');
        $responseOp->assertDontSee('Delivery Mode');

        $responseAdmin = $this->actingAs($admin)->get('/complaints');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Customer Name');
        $responseAdmin->assertSee('Delivery Mode');
    }

    public function test_can_update_all_complaint_fields_in_edit_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $complaint = \App\Models\BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'ORIGINAL123',
            'complaint_number' => 'EDIT-101',
            'case_number' => 'CRM-ORIGINAL',
            'complaint_date' => '2026-09-01',
            'customer_name' => 'Original Customer',
            'customer_mobile' => '9000000001',
            'location' => 'Original City',
            'product_model' => 'APBTZ4A',
            'product_segment' => 'TW',
            'purchase_date' => '2026-01-01',
            'vehicle_name' => 'Activa 5G',
            'vehicle_number' => 'GJ10AA1111',
            'delivery_mode' => 'BY HIMSELF',
            'complaint_status' => 'Open',
        ]);

        $response = $this->actingAs($admin)->put(route('complaints.update', $complaint->id), [
            'battery_type' => 'FW',
            'battery_serial_number' => 'UPDATED999',
            'complaint_number' => 'EDIT-101',
            'case_number' => 'CRM-UPDATED',
            'complaint_date' => '2026-09-05',
            'customer_name' => 'Updated Customer',
            'customer_mobile' => '9999999999',
            'location' => 'Jamnagar City',
            'product_model' => 'DIN74',
            'product_segment' => 'FW',
            'purchase_date' => '2026-02-15',
            'vehicle_name' => 'Hyundai Creta',
            'vehicle_number' => 'GJ10ZZ9999',
            'delivery_mode' => 'SEND IN ROUTE',
            'is_digital_warranty' => '1',
            'is_service_battery_given' => '1',
            'service_battery_number' => 'STANDBY888',
            'is_prorata' => '1',
            'prorata_percentage' => '45.00',
            'complaint_status' => 'Closed',
            'final_decision' => 'P',
            'decision_date' => '2026-09-10',
            'new_battery_number' => 'NEWREP777',
            'closing_date' => '2026-09-11',
            'return_date' => '2026-09-11',
        ]);

        $response->assertRedirect(route('complaints.show', $complaint->id));

        $this->assertDatabaseHas('battery_complaints', [
            'id' => $complaint->id,
            'battery_type' => 'FW',
            'battery_serial_number' => 'UPDATED999',
            'case_number' => 'CRM-UPDATED',
            'customer_name' => 'UPDATED CUSTOMER',
            'customer_mobile' => '9999999999',
            'location' => 'JAMNAGAR CITY',
            'product_model' => 'DIN74',
            'product_segment' => 'FW',
            'vehicle_name' => 'HYUNDAI CRETA',
            'vehicle_number' => 'GJ10ZZ9999',
            'delivery_mode' => 'SEND IN ROUTE',
            'is_digital_warranty' => true,
            'is_service_battery_given' => true,
            'service_battery_number' => 'STANDBY888',
            'is_prorata' => true,
            'prorata_percentage' => 45.00,
            'complaint_status' => 'Closed',
            'final_decision' => 'P',
            'new_battery_number' => 'NEWREP777',
        ]);
    }
}
