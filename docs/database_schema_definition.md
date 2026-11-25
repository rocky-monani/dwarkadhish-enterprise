# Database Schema Definition: TW & FW Battery Complaints

The **`battery_complaints`** table is the single unified database table in the application that stores all Two-Wheeler (`TW`) and Four-Wheeler/Inverter (`FW`) battery complaint records.

---

## 1. Primary Classification Column

| Field Name | Type | Default | Description |
|---|---|---|---|
| `battery_type` | `VARCHAR(10)` | `'TW'` | **Category Filter**: `'TW'` for Two Wheeler records, `'FW'` for Four Wheeler, Inverter, Commercial Vehicle, Tractor, or UPS records. |

---

## 2. Service Center Complaint Core Fields

| Field Name | Type | Nullable | Description |
|---|---|---|---|
| `id` | `BIGINT (PK)` | No | Auto-increment unique record ID. |
| `complaint_number` | `VARCHAR(100)` | No | Unique Service Center Complaint Number (e.g. `1001`, `3634`). Auto-incremented per category. |
| `complaint_date` | `DATE` | No | Date complaint was logged at the service center. |
| `case_number` | `VARCHAR(100)` | Yes | CRM Case Number (e.g. `13985794`, `CRM-984321`). |
| `customer_name` | `VARCHAR(150)` | No | Full Customer Name or Dealer Name (e.g. `MOMAI AUTO GARAGE`). |
| `customer_mobile` | `VARCHAR(25)` | Yes | Customer's 10-digit mobile phone number. |
| `location` | `VARCHAR(100)` | Yes | Customer Location / City / Village Name (e.g. `JAMNAGAR`, `LALPUR`). |
| `product_model` | `VARCHAR(100)` | Yes | Battery Model / Type (e.g. `APBTZ4A`, `BTX5L`, `DIN50R`). |

---

## 3. Vehicle & Sales Details (Auto-Fetched or Manual)

| Field Name | Type | Nullable | Description |
|---|---|---|---|
| `purchase_date` | `DATE` | Yes | Date of Sale / Battery Purchase Date. |
| `vehicle_name` | `VARCHAR(100)` | Yes | Vehicle Name / Model (e.g. `HERO SPLENDOR`, `ACTIVA`, `MARUTI SWIFT`). |
| `vehicle_number` | `VARCHAR(50)` | Yes | Vehicle Registration Number (e.g. `GJ37A4133`, `GJ10BN3539`). |
| `delivery_mode` | `VARCHAR(150)` | Yes | Delivery / Take Person Name & Mode (e.g. `BY HIMSELF`, `SEND IN ROUTE`, `RETAILER`). |

---

## 4. Battery Serial & Warranty Options

| Field Name | Type | Nullable | Description |
|---|---|---|---|
| `battery_serial_number` | `VARCHAR(100)` | No | Old / Defective Battery Serial Number scanned or entered (e.g. `EAB0206Z103054`). |
| `is_digital_warranty` | `TINYINT(1)` | No | `1` (true) if Digital Warranty registered on Battery Connect site, `0` (false) otherwise. |
| `is_service_battery_given` | `TINYINT(1)` | No | `1` (true) if Standby / Service Battery issued to customer, `0` (false) otherwise. |
| `service_battery_number` | `VARCHAR(100)` | Yes | Standby / Service Battery Serial Number issued. |
| `is_prorata` | `TINYINT(1)` | No | `1` (true) if Pro-Rata settlement applies, `0` (false) otherwise. |
| `prorata_percentage` | `DECIMAL(5,2)` | Yes | Pro-Rata discount percentage (e.g. `42.00`). |

---

## 5. Settlement & Closing Data

| Field Name | Type | Nullable | Description |
|---|---|---|---|
| `complaint_status` | `VARCHAR(50)` | No | Current complaint status: `'Open'`, `'Closed'`, or `'Rejected'`. |
| `final_decision` | `VARCHAR(100)` | Yes | Final Decision / Settlement Code (`'F'` = Free Replacement, `'P'` = Pro-Rata, `'C'` = Charging, `'R'` = Dead Return). |
| `decision_date` | `DATE` | Yes | Date final decision was recorded. |
| `new_battery_number` | `VARCHAR(100)` | Yes | Replacement / New Battery Serial Number issued upon closure. |
| `closing_date` | `DATE` | Yes | Date complaint was closed. |
| `return_date` | `DATE` | Yes | Date battery was returned to customer/dealer. |
| `closed_by` | `BIGINT (FK)` | Yes | User ID of operator who closed the complaint. |

---

## 6. Automation & Audit Tracking

| Field Name | Type | Nullable | Description |
|---|---|---|---|
| `battery_connect_image_path`| `VARCHAR(255)`| Yes | File path of auto-captured Battery Connect screenshot. |
| `battery_connect_lookup_at` | `TIMESTAMP` | Yes | Timestamp when Battery Connect lookup was performed. |
| `battery_connect_data` | `JSON` | Yes | Raw JSON metadata fetched from Battery Connect. |
| `created_by` | `BIGINT (FK)` | Yes | User ID of operator who created the record. |
| `is_deleted` | `TINYINT(1)` | No | `0` = Active record, `1` = Soft deleted. |
| `excel_sheet` | `VARCHAR(50)` | Yes | Source Excel sheet (`2W` or `4W`) if imported from workbook. |
| `excel_row_number` | `INT` | Yes | Source Excel row index if imported from workbook. |
| `created_at` | `TIMESTAMP` | Yes | Record creation timestamp. |
| `updated_at` | `TIMESTAMP` | Yes | Record last modification timestamp. |

---

## 7. Database Migration Reference Files

- Initial Table Structure: [`2026_08_26_000001_create_battery_complaints_table.php`](file:///d:/xampp/htdocs/dwarkadhish%20enterprise/database/migrations/2026_08_26_000001_create_battery_complaints_table.php)
- Extended Fields: [`2026_08_26_000003_add_extended_fields_to_battery_complaints_table.php`](file:///d:/xampp/htdocs/dwarkadhish%20enterprise/database/migrations/2026_08_26_000003_add_extended_fields_to_battery_complaints_table.php)
- Category Field (`battery_type`): [`2026_09_09_000002_add_battery_type_to_battery_complaints_table.php`](file:///d:/xampp/htdocs/dwarkadhish%20enterprise/database/migrations/2026_09_09_000002_add_battery_type_to_battery_complaints_table.php)
- Model Definition: [`BatteryComplaint.php`](file:///d:/xampp/htdocs/dwarkadhish%20enterprise/app/Models/BatteryComplaint.php)
