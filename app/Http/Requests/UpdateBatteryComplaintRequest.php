<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBatteryComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $complaintId = $this->route('id') ?? $this->route('battery_complaint');

        return [
            'battery_type' => 'nullable|string|in:TW,FW,tw,fw',
            'battery_serial_number' => 'required|string|max:100',
            'product_model' => 'nullable|string|max:100',
            'product_segment' => 'nullable|string|max:100',
            'complaint_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('battery_complaints', 'complaint_number')->ignore($complaintId),
            ],
            'case_number' => 'nullable|string|max:100',
            'complaint_date' => 'required|date',
            'customer_name' => 'required|string|max:150',
            'customer_mobile' => 'nullable|string|max:25',
            'location' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'vehicle_name' => 'nullable|string|max:100',
            'vehicle_number' => 'nullable|string|max:50',
            'delivery_mode' => 'nullable|string|max:150',
            'online_reg_type' => 'nullable|string|max:100',
            'is_digital_warranty' => 'nullable|boolean',
            'is_service_battery_given' => 'nullable|boolean',
            'service_battery_number' => 'required_if:is_service_battery_given,1|nullable|string|max:100',
            'is_prorata' => 'nullable|boolean',
            'prorata_percentage' => 'nullable|numeric|min:0|max:100',
            'complaint_status' => 'nullable|string|in:Open,Closed,Rejected',
            'final_decision' => 'nullable|string|max:100',
            'decision_date' => 'nullable|date',
            'new_battery_number' => 'nullable|string|max:100',
            'closing_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'battery_connect_image_path' => 'nullable|string|max:255',
            'manual_screenshot' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ];
    }
}
