<?php

namespace App\Http\Requests;

use App\Models\BatteryComplaint;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBatteryComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'battery_serial_number' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $cleanSerial = trim($value);
                    $openComplaint = BatteryComplaint::active()
                        ->where('battery_serial_number', $cleanSerial)
                        ->where('complaint_status', 'Open')
                        ->first();

                    if ($openComplaint) {
                        $dateStr = $openComplaint->complaint_date ? Carbon::parse($openComplaint->complaint_date)->format('d/m/Y') : 'previous date';
                        $fail("An open complaint (#{$openComplaint->complaint_number}) already exists for battery serial '{$cleanSerial}' (entered on {$dateStr}). Please close the previous case of this battery first before creating a new inward entry.");
                    }
                },
            ],
            'battery_type' => 'nullable|string|in:TW,FW,tw,fw',
            'product_model' => 'nullable|string|max:100',
            'product_segment' => 'nullable|string|max:100',
            'complaint_number' => 'required|string|max:100|unique:battery_complaints,complaint_number',
            'case_number' => 'nullable|string|max:100',
            'complaint_date' => 'required|date',
            'customer_name' => 'required|string|max:150',
            'customer_mobile' => 'nullable|string|max:25',
            'location' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'vehicle_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'online_reg_type' => 'nullable|string|max:100',
            'delivery_mode' => 'nullable|string|max:150',
            'is_digital_warranty' => 'nullable|boolean',
            'is_service_battery_given' => 'nullable|boolean',
            'service_battery_number' => 'required_if:is_service_battery_given,1|nullable|string|max:100',
            'is_prorata' => 'nullable|boolean',
            'prorata_percentage' => 'nullable|numeric|min:0|max:100',
            'battery_connect_image_path' => 'nullable|string|max:255',
            'manual_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'battery_serial_number.required' => 'Battery Serial Number is required.',
            'complaint_number.required' => 'Complaint Number is required.',
            'complaint_number.unique' => 'This Complaint Number already exists.',
            'complaint_date.required' => 'Complaint Date is required.',
            'customer_name.required' => 'Customer Name is required.',
            'service_battery_number.required_if' => 'Service Battery Number is required when Service Battery Issued is checked.',
            'prorata_percentage.min' => 'Pro-Rata Percentage must be between 0 and 100.',
            'prorata_percentage.max' => 'Pro-Rata Percentage must be between 0 and 100.',
        ];
    }
}
