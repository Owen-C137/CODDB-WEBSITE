<?php
// inc/_global/ajax_buy_item.php
require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json');

// 1) Must be logged in
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
if ($currentUserId === 0) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to purchase.']);
    exit;
}

// 2) CSRF check
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

// 3) Validate & fetch item
$itemId = (int)($_POST['item_id'] ?? 0);
if ($itemId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item.']);
    exit;
}
$itemStmt = $pdo->prepare("
    SELECT name, price
    FROM `demon_shop_items`
    WHERE id = :iid AND is_active = 1
    LIMIT 1
");
$itemStmt->execute(['iid' => $itemId]);
$item = $itemStmt->fetch(PDO::FETCH_ASSOC);
if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found or no longer available.']);
    exit;
}

// 4) Fetch user’s current balance
$balStmt = $pdo->prepare("
    SELECT credit_balance
    FROM `demon_users`
    WHERE id = :uid
    LIMIT 1
");
$balStmt->execute(['uid' => $currentUserId]);
$currentBalance = (int)$balStmt->fetchColumn();

if ($currentBalance < (int)$item['price']) {
    echo json_encode(['success' => false, 'message' => 'Insufficient credits.']);
    exit;
}

// 5) Deduct the price & insert log inside a transaction
try {
    $pdo->beginTransaction();

    // a) Deduct balance
    $upd = $pdo->prepare("
      UPDATE `demon_users`
      SET credit_balance = credit_balance - :price
      WHERE id = :uid
    ");
    $upd->execute([
      'price' => $item['price'],
      'uid'   => $currentUserId
    ]);

    // b) Insert into credit logs
    $logIns = $pdo->prepare("
      INSERT INTO `demon_credit_logs`
        (user_id, change_amount, type, description)
      VALUES
        (:uid, :amt, 'spend', :desc)
    ");
    $logIns->execute([
      'uid'  => $currentUserId,
      'amt'  => -1 * $item['price'],
      'desc' => "Purchased {$item['name']}"
    ]);

    // c) (Optional) Also insert into `demon_shop_purchases` if you track separate
    //     table for that. Example:
    // $purIns = $pdo->prepare("
    //   INSERT INTO `demon_shop_purchases`
    //     (user_id, item_id, price, purchased_at)
    //   VALUES
    //     (:uid, :iid, :price, NOW())
    // ");
    // $purIns->execute([
    //   'uid'   => $currentUserId,
    //   'iid'   => $itemId,
    //   'price' => $item['price']
    // ]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// 6) Return JSON + the new log entry (fetch last inserted log row)
$logFetch = $pdo->prepare("
  SELECT change_amount, type, description, created_at
  FROM `demon_credit_logs`
  WHERE user_id = :uid
  ORDER BY created_at DESC
  LIMIT 1
");
$logFetch->execute(['uid' => $currentUserId]);
$newLog = $logFetch->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'success' => true,
  'message' => "You purchased “{$item['name']}” for " . number_format($item['price']) . " credits!",
  'newLog'  => $newLog
]);
exit;
