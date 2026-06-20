# HWParts MNL — Supply Chain Management System

> **Version 1.0.0** | Built with CodeIgniter 4 · PHP 8.2+ · MySQL

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
email.fromName    = "HWParts MNL"
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

Proprietary — HWParts MNL. All rights reserved.
