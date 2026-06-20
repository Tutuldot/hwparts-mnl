<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes
$routes->get('/', 'Auth\AuthController::login');
$routes->get('auth/login', 'Auth\AuthController::login');
$routes->post('auth/login', 'Auth\AuthController::loginPost');
$routes->get('auth/logout', 'Auth\AuthController::logout');

// Customer Public Routes
$routes->get('customer-enrollment', 'CustomerController::enroll');
$routes->post('customer-enrollment', 'CustomerController::enrollPost');
$routes->get('customer/login', 'CustomerController::login');
$routes->post('customer/login', 'CustomerController::loginPost');
$routes->get('customer/logout', 'CustomerController::logout');

// Customer Protected Routes
$routes->group('customer', ['filter' => 'customer_auth'], function ($routes) {
    $routes->get('orders', 'CustomerController::orders');
    $routes->get('orders/(:num)', 'CustomerController::viewOrder/$1');
});

// Protected routes
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Parts & Categories
    $routes->get('parts', 'Parts\PartsController::index');
    $routes->get('parts/template', 'Parts\PartsController::template');
    $routes->post('parts/upload', 'Parts\PartsController::upload');
    $routes->get('parts/create', 'Parts\PartsController::create');
    $routes->post('parts/store', 'Parts\PartsController::store');
    $routes->get('parts/(:num)', 'Parts\PartsController::show/$1');
    $routes->get('parts/(:num)/edit', 'Parts\PartsController::edit/$1');
    $routes->post('parts/(:num)/update', 'Parts\PartsController::update/$1');
    $routes->post('parts/(:num)/toggle', 'Parts\PartsController::toggle/$1');
    $routes->get('parts/(:num)/print-label', 'Parts\PartsController::printLabel/$1');
    $routes->post('parts/(:num)/delete-photo/(:num)', 'Parts\PartsController::deletePhoto/$1/$2');
    $routes->post('parts/(:num)/set-primary-photo/(:num)', 'Parts\PartsController::setPrimaryPhoto/$1/$2');
    $routes->get('parts/ajax/sku-preview', 'Parts\PartsController::ajaxSkuPreview');
    $routes->get('parts/ajax/car-suggestions', 'Parts\PartsController::ajaxCarSuggestions');
    $routes->get('parts/ajax/brand-suggestions', 'Parts\PartsController::ajaxBrandSuggestions');
    $routes->get('parts/ajax/price', 'Parts\PartsController::ajaxGetPrice');

    $routes->get('categories', 'Parts\CategoryController::index');
    $routes->post('categories/store', 'Parts\CategoryController::store');
    $routes->post('categories/(:num)/update', 'Parts\CategoryController::update/$1');
    $routes->post('categories/(:num)/toggle', 'Parts\CategoryController::toggle/$1');

    $routes->get('parts/(:num)/variants', 'Parts\VariantController::index/$1');
    $routes->post('parts/(:num)/variants/store', 'Parts\VariantController::store/$1');
    $routes->post('variants/(:num)/update', 'Parts\VariantController::update/$1');
    $routes->post('variants/(:num)/toggle', 'Parts\VariantController::toggle/$1');

    // Inventory
    $routes->get('inventory', 'Inventory\InventoryController::index');
    $routes->get('inventory/create', 'Inventory\InventoryController::create');
    $routes->post('inventory/store', 'Inventory\InventoryController::store');
    $routes->get('inventory/(:num)', 'Inventory\InventoryController::show/$1');
    $routes->get('parts-details', 'Inventory\PartsDetailController::index');
    $routes->get('parts-details/(:num)', 'Inventory\PartsDetailController::show/$1');
    $routes->post('parts-details/(:num)/consume', 'Inventory\PartsDetailController::consume/$1');

    // Warehouses
    $routes->get('warehouses', 'Warehouse\WarehouseController::index');
    $routes->post('warehouses/store', 'Warehouse\WarehouseController::store');
    $routes->post('warehouses/(:num)/update', 'Warehouse\WarehouseController::update/$1');
    $routes->post('warehouses/(:num)/toggle', 'Warehouse\WarehouseController::toggle/$1');
    $routes->get('warehouses/(:num)/locations', 'Warehouse\WarehouseController::locations/$1');
    $routes->post('warehouses/(:num)/locations/store', 'Warehouse\WarehouseController::storeLocation/$1');
    $routes->post('warehouses/locations/(:num)/update', 'Warehouse\WarehouseController::updateLocation/$1');
    $routes->post('warehouses/locations/(:num)/toggle', 'Warehouse\WarehouseController::toggleLocation/$1');
    $routes->get('warehouses/ajax/locations', 'Warehouse\WarehouseController::ajaxLocations');

    // Stock Thresholds (Admin only)
    $routes->group('thresholds', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('/', 'Warehouse\StockThresholdController::index');
        $routes->post('store', 'Warehouse\StockThresholdController::store');
        $routes->post('(:num)/update', 'Warehouse\StockThresholdController::update/$1');
        $routes->post('(:num)/delete', 'Warehouse\StockThresholdController::delete/$1');
    });

    // Admin Settings (Admin only)
    $routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('settings', 'Admin\SettingsController::index');
        $routes->post('settings/send-test-email', 'Admin\SettingsController::sendTestEmail');
        $routes->get('users', 'Admin\UserController::index');
        $routes->post('users/store', 'Admin\UserController::store');
        $routes->post('users/(:num)/update', 'Admin\UserController::update/$1');
        $routes->post('users/(:num)/toggle', 'Admin\UserController::toggle/$1');
        $routes->post('users/(:num)/reset-password', 'Admin\UserController::resetPassword/$1');
    });

    // Transfers
    $routes->get('transfers', 'Transfer\TransferController::index');
    $routes->get('transfers/create', 'Transfer\TransferController::create');
    $routes->post('transfers/store', 'Transfer\TransferController::store');
    $routes->get('transfers/(:num)', 'Transfer\TransferController::view/$1');
    $routes->post('transfers/(:num)/submit', 'Transfer\TransferController::submit/$1');
    $routes->post('transfers/(:num)/approve', 'Transfer\TransferController::approve/$1');
    $routes->post('transfers/(:num)/reject', 'Transfer\TransferController::reject/$1');
    $routes->post('transfers/(:num)/transit', 'Transfer\TransferController::markInTransit/$1');
    $routes->post('transfers/(:num)/lines/(:num)/deliver', 'Transfer\TransferController::recordDelivery/$1/$2');
    $routes->post('transfers/(:num)/cancel', 'Transfer\TransferController::cancel/$1');
    $routes->get('transfers/ajax/available-stock', 'Transfer\TransferController::ajaxAvailableStock');
    $routes->get('transfers/ajax/available-units', 'Transfer\TransferController::ajaxAvailableUnits');

    // Purchase Orders
    $routes->get('purchase-orders', 'PurchaseOrder\POController::index');
    $routes->get('purchase-orders/create', 'PurchaseOrder\POController::create');
    $routes->post('purchase-orders/store', 'PurchaseOrder\POController::store');
    $routes->get('purchase-orders/(:num)', 'PurchaseOrder\POController::view/$1');
    $routes->get('purchase-orders/(:num)/edit', 'PurchaseOrder\POController::edit/$1');
    $routes->post('purchase-orders/(:num)/update', 'PurchaseOrder\POController::update/$1');
    $routes->post('purchase-orders/(:num)/submit', 'PurchaseOrder\POController::submit/$1');
    $routes->post('purchase-orders/(:num)/approve', 'PurchaseOrder\POController::approve/$1');
    $routes->post('purchase-orders/(:num)/reject', 'PurchaseOrder\POController::reject/$1');
    $routes->get('purchase-orders/(:num)/receive', 'PurchaseOrder\POController::receive/$1');
    $routes->post('purchase-orders/(:num)/receive-line', 'PurchaseOrder\POController::receiveLine/$1');
    $routes->post('purchase-orders/(:num)/cancel', 'PurchaseOrder\POController::cancel/$1');

    // Suppliers
    $routes->get('suppliers', 'SupplierController::index');
    $routes->get('suppliers/create', 'SupplierController::create');
    $routes->post('suppliers/store', 'SupplierController::store');
    $routes->post('suppliers/ajax-store', 'SupplierController::ajaxStore');
    $routes->get('suppliers/(:num)', 'SupplierController::show/$1');
    $routes->get('suppliers/(:num)/edit', 'SupplierController::edit/$1');
    $routes->post('suppliers/(:num)/update', 'SupplierController::update/$1');
    $routes->post('suppliers/(:num)/toggle', 'SupplierController::toggle/$1');

    // Accounts Payable
    $routes->get('accounts-payable', 'AccountsPayableController::index');
    $routes->get('accounts-payable/(:num)', 'AccountsPayableController::show/$1');
    $routes->post('accounts-payable/(:num)/pay', 'AccountsPayableController::pay/$1');
    $routes->post('accounts-payable/(:num)/resend-remittance', 'AccountsPayableController::resendRemittance/$1');

    // Customers (Admin end)
    $routes->get('customers', 'CustomerController::index');
    $routes->get('customers/create', 'CustomerController::create');
    $routes->post('customers/store', 'CustomerController::store');
    $routes->get('customers/(:num)', 'CustomerController::show/$1');
    $routes->get('customers/(:num)/edit', 'CustomerController::edit/$1');
    $routes->post('customers/(:num)/update', 'CustomerController::update/$1');
    $routes->post('customers/(:num)/toggle', 'CustomerController::toggle/$1');

    // Sales Orders
    $routes->get('sales-orders', 'SalesOrderController::index');
    $routes->get('sales-orders/create', 'SalesOrderController::create');
    $routes->post('sales-orders/store', 'SalesOrderController::store');
    $routes->get('sales-orders/(:num)', 'SalesOrderController::show/$1');
    $routes->post('sales-orders/(:num)/approve', 'SalesOrderController::approve/$1');
    $routes->post('sales-orders/(:num)/cancel', 'SalesOrderController::cancel/$1');
    $routes->get('sales-orders/ajax/search-parts', 'SalesOrderController::ajaxSearchParts');

    // Accounts Receivable
    $routes->get('accounts-receivable', 'AccountsReceivableController::index');
    $routes->get('accounts-receivable/(:num)', 'AccountsReceivableController::show/$1');
    $routes->post('accounts-receivable/(:num)/pay', 'AccountsReceivableController::pay/$1');
    $routes->post('accounts-receivable/(:num)/notice', 'AccountsReceivableController::notice/$1');

    // Admin users
    $routes->group('admin', ['filter' => 'role:admin'], function ($routes) {
        $routes->get('users', 'Admin\UserController::index');
        $routes->post('users/store', 'Admin\UserController::store');
        $routes->post('users/(:num)/update', 'Admin\UserController::update/$1');
        $routes->post('users/(:num)/toggle', 'Admin\UserController::toggle/$1');
        $routes->post('users/(:num)/reset-password', 'Admin\UserController::resetPassword/$1');
    });

    // Audit Logs
    $routes->get('audit-logs', 'AuditLogController::index');

    // Reports
    $routes->get('reports', 'ReportController::index');
    $routes->get('reports/access-matrix', 'ReportController::accessMatrix');
    $routes->post('reports/access-matrix/save', 'ReportController::saveAccess');
    $routes->get('reports/(:alpha)/export', 'ReportController::export/$1');
    $routes->get('reports/(:alpha)', 'ReportController::show/$1');
});
