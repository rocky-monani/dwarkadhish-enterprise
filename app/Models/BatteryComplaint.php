<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BatteryComplaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'battery_type',
        'battery_serial_number',
        'product_model',
        'product_segment',
        'complaint_number',
        'case_number',
        'complaint_date',
        'customer_name',
        'customer_mobile',
        'location',
        'purchase_date',
        'vehicle_name',
        'vehicle_number',
        'is_digital_warranty',
        'online_reg_type',
        'is_service_battery_given',
        'service_battery_number',
        'is_prorata',
        'prorata_percentage',
        'battery_connect_image_path',
        'battery_connect_lookup_at',
        'battery_connect_data',
        'created_by',
        'is_deleted',
        'complaint_status',
        'final_decision',
        'decision_date',
        'new_battery_number',
        'closing_date',
        'return_date',
        'delivery_mode',
        'closed_by',
        'excel_sheet',
        'excel_row_number',
        'print_count',
    ];

    protected $casts = [
        'complaint_date' => 'date',
        'purchase_date' => 'date',
        'decision_date' => 'date',
        'closing_date' => 'date',
        'return_date' => 'date',
        'is_prorata' => 'boolean',
        'is_digital_warranty' => 'boolean',
        'is_service_battery_given' => 'boolean',
        'prorata_percentage' => 'decimal:2',
        'battery_connect_lookup_at' => 'datetime',
        'battery_connect_data' => 'array',
        'is_deleted' => 'boolean',
        'print_count' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope to exclude soft-deleted items
     */
    public function scopeActive($query)
    {
        return $query->withoutTrashed();
    }

    /**
     * Check if the battery complaint record has replacement history (i.e. once replaced).
     * Returns true if Replacement History is NOT "Not Applicable."
     */
    public function hasReplacementHistory(): bool
    {
        $data = $this->battery_connect_data ?? [];

        if (!is_array($data)) {
            return false;
        }

        // 1. Check explicit boolean flag
        if (isset($data['is_replaced']) && $data['is_replaced'] === true) {
            return true;
        }

        // 2. Check replaced battery serial number field
        if (!empty($data['replaced_battery_sl_no']) || !empty($data['replaced_battery_serial'])) {
            $sl = trim($data['replaced_battery_sl_no'] ?? $data['replaced_battery_serial']);
            if ($sl !== '' && !preg_match('/not\s*applicable/i', $sl) && strtolower($sl) !== 'n/a') {
                return true;
            }
        }

        // 3. Check replacement_history field text
        if (!empty($data['replacement_history'])) {
            $rh = trim($data['replacement_history']);
            if ($rh !== '' && !preg_match('/not\s*applicable/i', $rh) && strtolower($rh) !== 'n/a' && strtolower($rh) !== 'false') {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate Lead Time 1 (number of days between Primary Billing / Manufacturing Date and Date of Sale)
     */
    public function getLeadTimeDaysAttribute(): ?int
    {
        $data = $this->battery_connect_data ?? [];

        if (is_array($data)) {
            // 1. Check direct lead_time_1 value extracted from Battery Connect portal
            $lt1 = $data['lead_time_1'] ?? $data['lead_time_1_days'] ?? $data['lead_time_days'] ?? $data['lead_time'] ?? null;
            if ($lt1 !== null) {
                if (is_numeric($lt1)) {
                    return (int) $lt1;
                }
                if (preg_match('/(\d+)/', (string) $lt1, $m)) {
                    return (int) $m[1];
                }
            }

            // 2. Calculate difference between Primary Billing / Manufacturing Date and Date of Sale (Purchase Date)
            $primaryBillingStr = $data['date_of_primary_billing'] ?? $data['primary_billing_date'] ?? $data['date_of_manufacturing'] ?? $data['manufacturing_date'] ?? null;
            $saleDateStr = $data['date_of_sale'] ?? $data['sale_date'] ?? ($this->purchase_date ? $this->purchase_date->format('Y-m-d') : null);

            if ($primaryBillingStr && $saleDateStr) {
                try {
                    $billingCarbon = \Carbon\Carbon::parse($primaryBillingStr);
                    $saleCarbon = \Carbon\Carbon::parse($saleDateStr);
                    return (int) abs($billingCarbon->diffInDays($saleCarbon));
                } catch (\Exception $e) {
                }
            }
        }

        return null;
    }

    /**
     * Check if Lead Time 1 exceeds 90 days
     */
    public function isLeadTimeExceeded(): bool
    {
        $days = $this->lead_time_days;
        return $days !== null && $days > 90;
    }

    /**
     * Scope for multi-column keyword search
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function ($q) use ($term) {
            $q->where('battery_serial_number', 'LIKE', "%{$term}%")
                ->orWhere('complaint_number', 'LIKE', "%{$term}%")
                ->orWhere('case_number', 'LIKE', "%{$term}%")
                ->orWhere('customer_name', 'LIKE', "%{$term}%")
                ->orWhere('customer_mobile', 'LIKE', "%{$term}%")
                ->orWhere('vehicle_number', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Relationship to creator user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to closing user
     */
    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Calculate next auto-incremented complaint number based on the latest record
     */
    public static function getNextComplaintNumber(?string $batteryType = null): string
    {
        $query = static::latest('id');

        if ($batteryType) {
            $query->where('battery_type', mb_strtoupper(trim($batteryType)));
        }

        $lastComplaint = $query->first();

        if ((! $lastComplaint || empty($lastComplaint->complaint_number)) && $batteryType) {
            $lastComplaint = static::latest('id')->first();
        }

        if (! $lastComplaint || empty($lastComplaint->complaint_number)) {
            return '1001';
        }

        $lastNo = trim($lastComplaint->complaint_number);

        if (preg_match('/^(.*?)(\d+)$/', $lastNo, $matches)) {
            $prefix = $matches[1];
            $digits = $matches[2];
            $len = strlen($digits);
            $nextNum = (int) $digits + 1;

            return $prefix.str_pad((string) $nextNum, $len, '0', STR_PAD_LEFT);
        }

        return $lastNo.'-1';
    }

    public function setBatteryTypeAttribute($value)
    {
        $this->attributes['battery_type'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : 'TW';
    }

    public function setBatterySerialNumberAttribute($value)
    {
        $this->attributes['battery_serial_number'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setProductModelAttribute($value)
    {
        $this->attributes['product_model'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setProductSegmentAttribute($value)
    {
        $this->attributes['product_segment'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setComplaintNumberAttribute($value)
    {
        $this->attributes['complaint_number'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setCaseNumberAttribute($value)
    {
        $this->attributes['case_number'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setCustomerNameAttribute($value)
    {
        $this->attributes['customer_name'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setCustomerMobileAttribute($value)
    {
        $this->attributes['customer_mobile'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setLocationAttribute($value)
    {
        $this->attributes['location'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setVehicleNameAttribute($value)
    {
        $this->attributes['vehicle_name'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setVehicleNumberAttribute($value)
    {
        $this->attributes['vehicle_number'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setOnlineRegTypeAttribute($value)
    {
        $this->attributes['online_reg_type'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setServiceBatteryNumberAttribute($value)
    {
        $this->attributes['service_battery_number'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setFinalDecisionAttribute($value)
    {
        $this->attributes['final_decision'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setNewBatteryNumberAttribute($value)
    {
        $this->attributes['new_battery_number'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }

    public function setDeliveryModeAttribute($value)
    {
        $this->attributes['delivery_mode'] = $value ? mb_strtoupper(trim($value), 'UTF-8') : null;
    }
}
