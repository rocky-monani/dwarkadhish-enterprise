<?php

namespace Tests\Feature;

use App\Models\BatteryComplaint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_1_entry_page_removes_crm_case_number_and_save_print_button(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/complaints/create');
        $response->assertStatus(200);

        // Verify CRM Case Number input and Save & Print button are removed from Operator 1 form
        $response->assertDontSee('CRM Case Number (Optional)');
        $response->assertDontSee('SAVE & PRINT');
        $response->assertSee('SAVE INWARD RECORD');
    }

    public function test_operator_1_can_save_inward_record_and_operator_2_views_pending_cases(): void
    {
        $user = User::factory()->create();

        // 1. Operator 1 saves inward entry
        $response = $this->actingAs($user)->post('/complaints', [
            'battery_type' => 'TW',
            'battery_serial_number' => 'EAB0206Z109876',
            'complaint_number' => '3999',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'OPERATOR WORKFLOW CUSTOMER',
            'product_model' => 'BTZ4L',
        ]);

        $response->assertRedirect(route('complaints.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('battery_complaints', [
            'complaint_number' => '3999',
            'customer_name' => 'OPERATOR WORKFLOW CUSTOMER',
            'case_number' => 'NO ONLINE',
            'print_count' => 0,
        ]);

        // 2. Operator 2 opens Pending Cases page
        $pendingResponse = $this->actingAs($user)->get('/complaints/pending-cases');
        $pendingResponse->assertSee('PENDING CASES');
        $pendingResponse->assertSee('OPERATOR WORKFLOW CUSTOMER');
        $pendingResponse->assertSee('3999');
        $pendingResponse->assertSee('PRINT ALL TODAY\'S ENTRY', false);

        $complaint = BatteryComplaint::where('complaint_number', '3999')->first();

        // 3. Operator 2 updates CRM Case Number
        $caseUpdateResponse = $this->actingAs($user)->post("/complaints/{$complaint->id}/case-number", [
            'case_number' => 'CRM-888999',
        ]);

        $caseUpdateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('battery_complaints', [
            'id' => $complaint->id,
            'case_number' => 'CRM-888999',
        ]);
    }

    public function test_batch_print_prints_today_unprinted_entries_and_increments_print_counter(): void
    {
        $user = User::factory()->create();

        $c1 = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'SER11111',
            'complaint_number' => '5001',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'BATCH ONE',
            'print_count' => 0,
        ]);

        $c2 = BatteryComplaint::create([
            'battery_type' => 'FW',
            'battery_serial_number' => 'SER22222',
            'complaint_number' => '5002',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'BATCH TWO',
            'print_count' => 0,
        ]);

        // Trigger batch print for today's unprinted entries
        $batchResponse = $this->actingAs($user)->get('/complaints/print-batch-today');
        $batchResponse->assertStatus(200);

        // Verify complaint numbers in print view DO NOT have leading #
        $batchResponse->assertSee('5001');
        $batchResponse->assertSee('5002');
        $batchResponse->assertDontSee('#5001');
        $batchResponse->assertDontSee('#5002');

        // Verify print_count incremented for both records
        $this->assertEquals(1, $c1->fresh()->print_count);
        $this->assertEquals(1, $c2->fresh()->print_count);
    }

    public function test_single_entry_print_allows_reprint_and_removes_hashtag_prefix(): void
    {
        $user = User::factory()->create();

        $complaint = BatteryComplaint::create([
            'battery_type' => 'TW',
            'battery_serial_number' => 'SER33333',
            'complaint_number' => '5003',
            'complaint_date' => date('Y-m-d'),
            'customer_name' => 'SINGLE PRINT CUSTOMER',
            'print_count' => 1, // Already printed once
        ]);

        // Print single entry again
        $printResponse = $this->actingAs($user)->get("/complaints/{$complaint->id}/print");
        $printResponse->assertStatus(200);

        // Verify no leading hashtag in print view header
        $printResponse->assertSee('5003');
        $printResponse->assertDontSee('#5003');

        // Counter incremented from 1 to 2
        $this->assertEquals(2, $complaint->fresh()->print_count);
    }
}
