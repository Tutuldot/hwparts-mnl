<?php
try {
    $pdo = new PDO(
        'mysql:host=srv2115.hstgr.io;dbname=u817456591_partsmaster;charset=utf8mb4',
        'u817456591_partsmaster',
        'PH7=oq!~2=jX',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Test 1: Fixed low stock query
    $sql = "
        SELECT p.id as part_id, p.name as part_name, p.sku,
               w.id as warehouse_id, w.name as warehouse_name, w.code as warehouse_code,
               COALESCE(SUM(il.quantity), 0) as current_stock,
               COALESCE(pst.min_stock_level, p.min_stock_level) as threshold
        FROM parts p
        JOIN warehouses w ON w.is_active = 1
        LEFT JOIN part_stock_thresholds pst
            ON pst.part_id = p.id AND pst.warehouse_id = w.id AND pst.is_active = 1
        LEFT JOIN inventory_lines il
            ON il.part_id = p.id AND il.warehouse_id = w.id
        WHERE p.type = 'quantity' AND p.is_active = 1
        GROUP BY p.id, p.name, p.sku, w.id, w.name, w.code, pst.min_stock_level, p.min_stock_level
        HAVING COALESCE(SUM(il.quantity), 0) <= COALESCE(pst.min_stock_level, p.min_stock_level)
           AND COALESCE(pst.min_stock_level, p.min_stock_level) > 0
        ORDER BY (COALESCE(SUM(il.quantity), 0) - COALESCE(pst.min_stock_level, p.min_stock_level)) ASC
    ";
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "PASS: Low stock query - " . count($rows) . " alert(s)" . PHP_EOL;

    // Test 2: Warehouse with location count
    $sql2 = "
        SELECT w.id, w.code, w.name, w.address, w.contact_person, w.contact_number,
               w.is_active, w.created_by, w.created_at, w.updated_at,
               COUNT(wl.id) as location_count
        FROM warehouses w
        LEFT JOIN warehouse_locations wl ON wl.warehouse_id = w.id AND wl.is_active = 1
        GROUP BY w.id, w.code, w.name, w.address, w.contact_person, w.contact_number,
                 w.is_active, w.created_by, w.created_at, w.updated_at
        ORDER BY w.name ASC
    ";
    $rows2 = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
    echo "PASS: Warehouse query - " . count($rows2) . " warehouse(s)" . PHP_EOL;
    foreach ($rows2 as $r) {
        echo "  - " . $r['name'] . " (" . $r['location_count'] . " sub-locations)" . PHP_EOL;
    }

    // Test 3: Admin user password
    $stmt = $pdo->query("SELECT id, name, email, role, is_active, password FROM users WHERE email='admin@hwparts.com'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "PASS: Admin user found - Active=" . $user['is_active'] . " Role=" . $user['role'] . PHP_EOL;
        echo "  Admin@1234 match:   " . (password_verify('Admin@1234',   $user['password']) ? 'YES' : 'NO') . PHP_EOL;
        echo "  Admin@123456 match: " . (password_verify('Admin@123456', $user['password']) ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo "FAIL: Admin user NOT found" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
