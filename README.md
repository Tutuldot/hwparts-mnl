# HW Trucks MNL — Supply Chain Management System

> **Version 1.0.1** | Built with CodeIgniter 4 · PHP 8.2+ · MySQL

A full-featured supply chain and inventory management system tailored for hardware parts distributors. Covers the complete business flow from procurement to sales, with accounts payable/receivable, customer management, barcode POS, and business reporting.

---

## ✨ Features

### 📦 Parts & Inventory
- **Parts Catalog** — SKU auto-generation, categories, OEM flag, brand, unit of measure, min stock levels
- **Variants** — per-part variant SKUs (e.g. sizes, specs) with independent pricing
- **Barcode & QR Codes** — auto-generated on part creation; printable label support
- **Bulk CSV Import** — import parts from spreadsheet template
- **Selling Price Management** — set a single active selling price (+ minimum price) per part and per variant
- **Multi-Warehouse Stock** — track stock levels across multiple warehouses and bin locations
- **Vehicle Compatibility Tags** — link parts to car brands/models
- **Photo Gallery** — upload multiple photos per part with primary photo selection

### 🏭 Warehouses & Transfers
- **Warehouse Management** — create warehouses and sub-locations
- **Inventory Receipts** — receive stock from Purchase Orders into specific bin locations; FIFO batch tracking
- **Inter-Warehouse Transfers** — request, submit, approve, and fulfill stock transfers between warehouses
- **Stock Thresholds** — configurable low-stock alerts per part per warehouse

### 🛒 Purchase Orders (Procurement)
- **PO Creation** — select supplier, add line items with quantities and unit costs
- **Payment Due Date** — set per-PO; auto-applied to AP on approval
- **Approval Workflow** — Draft → Submitted → Approved → Received
- **Goods Receipt** — line-by-line receiving into warehouse locations
- **Supplier Management** — supplier directory with contact emails and tags

### 💰 Accounts Payable
- Auto-created on PO approval, linked to supplier and PO
- Payment methods: **GCash, Bank Transfer, Cheque** (with bank + cheque number), **Cash via Transmittal**
- Mandatory **proof of payment** photo upload
- **Reference number** tracking per payment
- **Remittance Advice** — professional HTML email sent to supplier on payment; SMS notification (dummy API)
- Actual payment amount input with a **MAX** button to auto-fill from PO amount
- Notification history per AP record
- Supplier-provided **Invoice Number** field

### 🛍️ Sales (POS & Order Management)
- **Customer Database** — Individual or Corporate; billing/shipping address, TIN, payment terms
- **Customer Self-Enrollment** — public-facing enrollment URL; customers set their own username/password
- **Barcode/QR POS Interface** — scan via USB scanner or device camera (browser-based)
- **Part Search** — live search with suggested selling price auto-fill from pricing module
- **Per-Line Discounts** — apply percentage (%) or fixed amount (₱) discounts per cart item
- **Sales Order Approval** — Draft → Approved; auto-generates AR invoice with BIR-sequential numbering
- **FIFO COGS Tracking** — on approval, acquisition cost is allocated from oldest inventory batches

### 📥 Accounts Receivable
- Auto-created on SO approval; linked to customer and sales order
- Payment recording with actual amount input
- Accounts receivable aging tracked with due dates

### 📊 Reports
Eight downloadable Excel reports with preview and filter support:

| Report | Description |
|--------|-------------|
| Sales Summary | All SOs with gross, discount, net amounts |
| Sales by Part | Revenue and quantity per part/variant |
| AR Aging | Receivables bucketed 0–30 / 31–60 / 61–90 / 90+ days |
| AP Aging | Payables bucketed by aging |
| Purchase Orders Summary | POs with supplier, amounts, dates |
| Inventory Stock Levels | On-hand, consumed, available per part/warehouse |
| Price List | All parts and variants with current selling prices |
| Customer Ledger | Per-customer SO count, billed, paid, balance |

**Report Access Matrix** — admin can assign which roles can view/export each report.

### 👤 Users & Admin
- Role-based access: **Admin, Warehouse, Purchasing, Approver**
- User management (create, edit, toggle, reset password)
- **System Settings** — view SMTP configuration, send test emails
- **Audit Log** — every create/update/approve/delete action is logged with user and timestamp

---

## 🗂️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | CodeIgniter 4.7 |
| Language | PHP 8.2+ |
| Database | MySQL 8.0+ |
| Frontend | Bootstrap 5.3, Font Awesome 6.5, DataTables |
| Fonts | Inter (Google Fonts) |
| Email | Hostinger SMTP (configurable via `.env`) |
| Excel Export | PhpOffice/PhpSpreadsheet |
| QR Codes | chillerlan/php-qrcode |

---

## 🚀 Installation

### Requirements
- PHP 8.2+ with extensions: `intl`, `mbstring`, `json`, `mysqlnd`, `curl`
- MySQL 8.0+
- Composer

### Steps

```bash
# 1. Clone the repository
git clone <repo-url> hwparts-mnl
cd hwparts-mnl

# 2. Install dependencies
composer install

# 3. Copy environment file
cp env .env

# 4. Configure .env
#    Set CI_ENVIRONMENT, app.baseURL, database credentials, and SMTP settings
nano .env

# 5. Run database migrations
php spark migrate

# 6. Start development server
php spark serve
```

### Environment Configuration (`.env`)

```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = hwparts_mnl
database.default.username = root
database.default.password = yourpassword
database.default.DBDriver = MySQLi

# SMTP (Hostinger example)
email.SMTPHost    = smtp.hostinger.com
email.SMTPPort    = 465
email.SMTPCrypto  = ssl
email.SMTPUser    = you@yourdomain.com
email.SMTPPass    = yourpassword
email.fromEmail   = you@yourdomain.com
email.fromName    = "HW Trucks MNL"
```

---

## 🗄️ Database

Migrations are managed via CodeIgniter's migration system. Run all pending migrations with:

```bash
php spark migrate
```

To roll back:

```bash
php spark migrate:rollback
```

---

## 📁 Project Structure

```
app/
├── Config/
│   ├── Routes.php          # All application routes
│   └── Email.php           # SMTP mail config
├── Controllers/
│   ├── Admin/              # User management, Settings
│   ├── Auth/               # Login / Logout
│   ├── Inventory/          # Inventory receipts, parts details
│   ├── Parts/              # Parts CRUD, variants, categories
│   ├── PurchaseOrder/      # PO workflow
│   ├── Transfer/           # Inter-warehouse transfers
│   ├── Warehouse/          # Warehouse & location management
│   ├── AccountsPayableController.php
│   ├── AccountsReceivableController.php
│   ├── CustomerController.php
│   ├── ReportController.php
│   └── SalesOrderController.php
├── Models/                 # One model per table + PartPriceModel
├── Views/
│   ├── layouts/main.php    # Master layout with sidebar
│   ├── parts/              # Parts show, edit, variants
│   ├── sales_order/        # POS create, show, list
│   ├── accounts_payable/
│   ├── accounts_receivable/
│   ├── reports/            # index, show, access_matrix
│   └── admin/              # users, settings
├── Filters/
│   ├── AuthFilter.php      # Redirect if not logged in
│   └── RoleFilter.php      # Role-based access control
└── Database/
    └── Migrations/         # Versioned schema changes
```

---

## 🛣️ Application Routes

### 🌐 Public & Auth Routes
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `/` | `Auth\AuthController::login` | Redirects to login |
| GET | `auth/login` | `Auth\AuthController::login` | Admin login page |
| POST | `auth/login` | `Auth\AuthController::loginPost` | Process admin login |
| GET | `auth/logout` | `Auth\AuthController::logout` | Logout admin |
| GET | `customer-enrollment` | `CustomerController::enroll` | Customer self-enrollment page |
| POST | `customer-enrollment` | `CustomerController::enrollPost` | Process customer registration |
| GET | `customer/login` | `CustomerController::login` | Customer portal login page |
| POST | `customer/login` | `CustomerController::loginPost` | Process customer login |
| GET | `customer/logout` | `CustomerController::logout` | Logout customer |

### 👤 Customer Portal (Protected)
Requires customer authentication (`customer_auth` filter).

| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `customer/orders` | `CustomerController::orders` | List customer's own Sales Orders |
| GET | `customer/orders/(:num)` | `CustomerController::viewOrder/$1` | View customer's specific Sales Order |
| GET | `customer/inquiries` | `InquiryController::customerIndex` | List customer's support inquiries |
| POST | `customer/inquiries/create` | `InquiryController::customerCreate` | Create a new support inquiry |
| GET | `customer/inquiries/(:num)` | `InquiryController::customerShow/$1` | View inquiry message thread |
| POST | `customer/inquiries/(:num)/message` | `InquiryController::customerMessage/$1` | Send text or photo reply in inquiry thread |

### 🏢 Internal Dashboard (Protected)
Requires standard staff/admin authentication (`auth` filter).

#### Dashboard & Settings
| Method | Route | Controller Action | Description | Filter |
|--------|-------|-------------------|-------------|--------|
| GET | `dashboard` | `DashboardController::index` | SCM dashboard home | None |
| GET | `admin/settings` | `Admin\SettingsController::index` | System config & settings | `role:admin` |
| POST | `admin/settings/send-test-email` | `Admin\SettingsController::sendTestEmail` | Trigger test email | `role:admin` |

#### User Management
| Method | Route | Controller Action | Description | Filter |
|--------|-------|-------------------|-------------|--------|
| GET | `admin/users` | `Admin\UserController::index` | List system users | `role:admin` |
| POST | `admin/users/store` | `Admin\UserController::store` | Save new user | `role:admin` |
| POST | `admin/users/(:num)/update` | `Admin\UserController::update/$1` | Update user | `role:admin` |
| POST | `admin/users/(:num)/toggle` | `Admin\UserController::toggle/$1` | Toggle user status | `role:admin` |
| POST | `admin/users/(:num)/reset-password` | `Admin\UserController::resetPassword/$1` | Reset user password | `role:admin` |

#### Catalogue (Parts & Categories)
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `parts` | `Parts\PartsController::index` | Parts list catalogue |
| GET | `parts/template` | `Parts\PartsController::template` | Download CSV template |
| POST | `parts/upload` | `Parts\PartsController::upload` | Import parts from CSV |
| GET | `parts/create` | `Parts\PartsController::create` | Create new part form |
| POST | `parts/store` | `Parts\PartsController::store` | Save new part |
| GET | `parts/(:num)` | `Parts\PartsController::show/$1` | View part details |
| GET | `parts/(:num)/edit` | `Parts\PartsController::edit/$1` | Edit part form |
| POST | `parts/(:num)/update` | `Parts\PartsController::update/$1` | Update part |
| POST | `parts/(:num)/toggle` | `Parts\PartsController::toggle/$1` | Toggle active status |
| GET | `parts/(:num)/print-label` | `Parts\PartsController::printLabel/$1` | Print printable barcode label |
| POST | `parts/(:num)/delete-photo/(:num)` | `Parts\PartsController::deletePhoto/$1/$2` | Delete part photo |
| POST | `parts/(:num)/set-primary-photo/(:num)` | `Parts\PartsController::setPrimaryPhoto/$1/$2` | Set primary part photo |
| GET | `parts/ajax/sku-preview` | `Parts\PartsController::ajaxSkuPreview` | SKU verification helper |
| GET | `parts/ajax/car-suggestions` | `Parts\PartsController::ajaxCarSuggestions` | Search car tag autocomplete |
| GET | `parts/ajax/brand-suggestions` | `Parts\PartsController::ajaxBrandSuggestions` | Search brand autocomplete |
| GET | `parts/ajax/price` | `Parts\PartsController::ajaxGetPrice` | Fetch price for invoice |
| GET | `categories` | `Parts\CategoryController::index` | List categories |
| POST | `categories/store` | `Parts\CategoryController::store` | Save category |
| POST | `categories/(:num)/update` | `Parts\CategoryController::update/$1` | Update category |
| POST | `categories/(:num)/toggle` | `Parts\CategoryController::toggle/$1` | Toggle category |
| GET | `parts/(:num)/variants` | `Parts\VariantController::index/$1` | List variants of a part |
| POST | `parts/(:num)/variants/store` | `Parts\VariantController::store/$1` | Create variant |
| POST | `variants/(:num)/update` | `Parts\VariantController::update/$1` | Update variant |
| POST | `variants/(:num)/toggle` | `Parts\VariantController::toggle/$1` | Toggle variant |

#### Inventory & Warehouses
| Method | Route | Controller Action | Description | Filter |
|--------|-------|-------------------|-------------|--------|
| GET | `inventory` | `Inventory\InventoryController::index` | Inventory ledger index | None |
| GET | `inventory/create` | `Inventory\InventoryController::create` | Create stock receipt | None |
| POST | `inventory/store` | `Inventory\InventoryController::store` | Save stock receipt | None |
| GET | `inventory/(:num)` | `Inventory\InventoryController::show/$1` | View stock receipt details | None |
| GET | `parts-details` | `Inventory\PartsDetailController::index` | Individual tracked units | None |
| GET | `parts-details/(:num)` | `Inventory\PartsDetailController::show/$1` | View tracked unit details | None |
| POST | `parts-details/(:num)/consume` | `Inventory\PartsDetailController::consume/$1` | Consume a tracked unit | None |
| GET | `warehouses` | `Warehouse\WarehouseController::index` | Warehouse directory | None |
| POST | `warehouses/store` | `Warehouse\WarehouseController::store` | Save warehouse | None |
| POST | `warehouses/(:num)/update` | `Warehouse\WarehouseController::update/$1` | Update warehouse | None |
| POST | `warehouses/(:num)/toggle` | `Warehouse\WarehouseController::toggle/$1` | Toggle warehouse | None |
| GET | `warehouses/(:num)/locations` | `Warehouse\WarehouseController::locations/$1` | List sub-locations | None |
| POST | `warehouses/(:num)/locations/store` | `Warehouse\WarehouseController::storeLocation/$1` | Create sub-location | None |
| POST | `warehouses/locations/(:num)/update` | `Warehouse\WarehouseController::updateLocation/$1` | Update sub-location | None |
| POST | `warehouses/locations/(:num)/toggle` | `Warehouse\WarehouseController::toggleLocation/$1` | Toggle sub-location | None |
| GET | `warehouses/ajax/locations` | `Warehouse\WarehouseController::ajaxLocations` | Fetch locations in selector | None |
| GET | `thresholds` | `Warehouse\StockThresholdController::index` | List stock thresholds | `role:admin` |
| POST | `thresholds/store` | `Warehouse\StockThresholdController::store` | Save stock threshold | `role:admin` |
| POST | `thresholds/(:num)/update` | `Warehouse\StockThresholdController::update/$1` | Update stock threshold | `role:admin` |
| POST | `thresholds/(:num)/delete` | `Warehouse\StockThresholdController::delete/$1` | Delete stock threshold | `role:admin` |

#### Inter-Warehouse Transfers
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `transfers` | `Transfer\TransferController::index` | List stock transfers |
| GET | `transfers/create` | `Transfer\TransferController::create` | Request transfer form |
| POST | `transfers/store` | `Transfer\TransferController::store` | Submit transfer request |
| GET | `transfers/(:num)` | `Transfer\TransferController::view/$1` | View transfer details |
| POST | `transfers/(:num)/submit` | `Transfer\TransferController::submit/$1` | Submit transfer request |
| POST | `transfers/(:num)/approve` | `Transfer\TransferController::approve/$1` | Approve transfer request |
| POST | `transfers/(:num)/reject` | `Transfer\TransferController::reject/$1` | Reject transfer request |
| POST | `transfers/(:num)/transit` | `Transfer\TransferController::markInTransit/$1` | Set transfer as in-transit |
| POST | `transfers/(:num)/lines/(:num)/deliver` | `Transfer\TransferController::recordDelivery/$1/$2` | Record received transfer items |
| POST | `transfers/(:num)/cancel` | `Transfer\TransferController::cancel/$1` | Cancel transfer request |
| GET | `transfers/ajax/available-stock` | `Transfer\TransferController::ajaxAvailableStock` | Fetch available stock |
| GET | `transfers/ajax/available-units` | `Transfer\TransferController::ajaxAvailableUnits` | Fetch individual units |

#### Procurement (Purchase Orders & Suppliers)
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `purchase-orders` | `PurchaseOrder\POController::index` | List Purchase Orders |
| GET | `purchase-orders/create` | `PurchaseOrder\POController::create` | Create PO form |
| POST | `purchase-orders/store` | `PurchaseOrder\POController::store` | Save PO |
| GET | `purchase-orders/(:num)` | `PurchaseOrder\POController::view/$1` | View PO details |
| GET | `purchase-orders/(:num)/edit` | `PurchaseOrder\POController::edit/$1` | Edit PO form |
| POST | `purchase-orders/(:num)/update` | `PurchaseOrder\POController::update/$1` | Update PO |
| POST | `purchase-orders/(:num)/submit` | `PurchaseOrder\POController::submit/$1` | Submit PO for approval |
| POST | `purchase-orders/(:num)/approve` | `PurchaseOrder\POController::approve/$1` | Approve PO |
| POST | `purchase-orders/(:num)/reject` | `PurchaseOrder\POController::reject/$1` | Reject PO |
| GET | `purchase-orders/(:num)/receive` | `PurchaseOrder\POController::receive/$1` | PO Goods receipt page |
| POST | `purchase-orders/(:num)/receive-line` | `PurchaseOrder\POController::receiveLine/$1` | Fulfill PO line item |
| POST | `purchase-orders/(:num)/cancel` | `PurchaseOrder\POController::cancel/$1` | Cancel PO |
| GET | `suppliers` | `SupplierController::index` | Supplier directory |
| GET | `suppliers/create` | `SupplierController::create` | Add supplier form |
| POST | `suppliers/store` | `SupplierController::store` | Save supplier |
| POST | `suppliers/ajax-store` | `SupplierController::ajaxStore` | Quick-add supplier |
| GET | `suppliers/(:num)` | `SupplierController::show/$1` | View supplier profile |
| GET | `suppliers/(:num)/edit` | `SupplierController::edit/$1` | Edit supplier form |
| POST | `suppliers/(:num)/update` | `SupplierController::update/$1` | Update supplier |
| POST | `suppliers/(:num)/toggle` | `SupplierController::toggle/$1` | Toggle supplier active status |

#### Accounts Payable
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `accounts-payable` | `AccountsPayableController::index` | Accounts Payable directory |
| GET | `accounts-payable/(:num)` | `AccountsPayableController::show/$1` | View AP record and remittance log |
| POST | `accounts-payable/(:num)/pay` | `AccountsPayableController::pay/$1` | Record payment, upload proof, send remittance |
| POST | `accounts-payable/(:num)/resend-remittance` | `AccountsPayableController::resendRemittance/$1` | Trigger email resend |

#### Sales Orders & Customers
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `customers` | `CustomerController::index` | Customer directory |
| GET | `customers/create` | `CustomerController::create` | Add customer form |
| POST | `customers/store` | `CustomerController::store` | Save customer |
| GET | `customers/(:num)` | `CustomerController::show/$1` | View customer detail & balance ledger |
| GET | `customers/(:num)/edit` | `CustomerController::edit/$1` | Edit customer profile |
| POST | `customers/(:num)/update` | `CustomerController::update/$1` | Update customer details |
| POST | `customers/(:num)/toggle` | `CustomerController::toggle/$1` | Toggle customer status |
| GET | `sales-orders` | `SalesOrderController::index` | List Sales Orders |
| GET | `sales-orders/create` | `SalesOrderController::create` | Create Sales Order POS cash register |
| POST | `sales-orders/store` | `SalesOrderController::store` | Save POS Sales Order draft |
| GET | `sales-orders/(:num)` | `SalesOrderController::show/$1` | View invoice details |
| POST | `sales-orders/(:num)/approve` | `SalesOrderController::approve/$1` | Approve order, generate AR, deduce stock (FIFO) |
| POST | `sales-orders/(:num)/cancel` | `SalesOrderController::cancel/$1` | Cancel draft Sales Order |
| GET | `sales-orders/ajax/search-parts` | `SalesOrderController::ajaxSearchParts` | Search autocomplete inside POS |

#### Accounts Receivable
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `accounts-receivable` | `AccountsReceivableController::index` | Accounts Receivable ledger |
| GET | `accounts-receivable/(:num)` | `AccountsReceivableController::show/$1` | View invoice status and payment inputs |
| POST | `accounts-receivable/(:num)/pay` | `AccountsReceivableController::pay/$1` | Record customer payment |
| POST | `accounts-receivable/(:num)/notice` | `AccountsReceivableController::notice/$1` | Send AR warning reminder notice email |

#### Customer Support Inquiries
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `admin/inquiries` | `InquiryController::adminIndex` | List all inquiries |
| GET | `admin/inquiries/(:num)` | `InquiryController::adminShow/$1` | Admin detail thread & SO assignment page |
| POST | `admin/inquiries/(:num)/message` | `InquiryController::adminMessage/$1` | Send response message / photo to customer |
| POST | `admin/inquiries/(:num)/close` | `InquiryController::adminClose/$1` | Mark thread resolved & close inquiry |
| POST | `admin/inquiries/(:num)/assign-so` | `InquiryController::adminAssignSo/$1` | Manually link existing customer SO to inquiry |

#### Business Reports & Audits
| Method | Route | Controller Action | Description |
|--------|-------|-------------------|-------------|
| GET | `reports` | `ReportController::index` | Excel reports directory |
| GET | `reports/access-matrix` | `ReportController::accessMatrix` | Report roles configurations matrix |
| POST | `reports/access-matrix/save` | `ReportController::saveAccess` | Save configurations matrix |
| GET | `reports/(:alpha)/export` | `ReportController::export/$1` | Trigger Excel download export |
| GET | `reports/(:alpha)` | `ReportController::show/$1` | Live browser data preview list |
| GET | `audit-logs` | `AuditLogController::index` | List user audit logs history |

---

## 🔐 Default Roles

| Role | Access |
|------|--------|
| `admin` | Full access to all modules + user management + settings + reports |
| `approver` | Approve POs, SOs, Transfers; view all |
| `purchasing` | POs, suppliers, AP; no admin |
| `warehouse` | Inventory, transfers, parts; no financial |

---

## 📧 Email Features

- **Remittance Advice** — sent to suppliers on AP payment with payment breakdown
- **Customer Notices** — AR reminders
- **Test Email Tool** — Admin → Settings → Send Test Email to verify SMTP configuration

---

## 📄 License

Proprietary — HW Trucks MNL. All rights reserved.
