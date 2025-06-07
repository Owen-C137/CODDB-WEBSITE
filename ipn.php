<?php
// ipn.php – PayPal Instant Payment Notification handler

// ───────────────────────────────────────────────────────────
// 1) Bootstrap your config (defines PAYPAL_ENV, sets up $pdo)
// ───────────────────────────────────────────────────────────
require __DIR__ . '/inc/_global/config.php';



// ───────────────────────────────────────────────────────────
// 2) Read POST from PayPal
// ───────────────────────────────────────────────────────────
$raw = file_get_contents('php://input');
parse_str($raw, $data);

// ───────────────────────────────────────────────────────────
// 3) Post back to PayPal for validation
// ───────────────────────────────────────────────────────────
$endpoint = PAYPAL_ENV === 'live'
    ? 'https://ipnpb.paypal.com/cgi-bin/webscr'
    : 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => 'cmd=_notify-validate&' . $raw,
]);
$res = curl_exec($ch);
curl_close($ch);

// If PayPal didn’t verify it, quit
if (strcmp($res, "VERIFIED") !== 0) {
    http_response_code(400);
    exit;
}

// ───────────────────────────────────────────────────────────
// 4) Only process completed payments
// ───────────────────────────────────────────────────────────
if (($data['payment_status'] ?? '') !== 'Completed') {
    exit;
}

// ───────────────────────────────────────────────────────────
// 5) Extract the info we need
// ───────────────────────────────────────────────────────────
$userId   = (int) ($data['custom']     ?? 0);
$amount   = (float) ($data['mc_gross']  ?? 0);
$currency = $data['mc_currency']       ?? '';
$txnId    = $data['txn_id']            ?? '';

// ───────────────────────────────────────────────────────────
// 6) Record to demon_donations and upgrade demon_users
// ───────────────────────────────────────────────────────────
if ($userId && $amount > 0) {
    $pdo->beginTransaction();

    // Avoid duplicates
    $pdo->prepare("
      INSERT IGNORE INTO demon_donations
        (user_id, amount, currency, payment_method, gateway_reference, status)
      VALUES
        (:uid, :amt, :cur, 'paypal', :txn, 'paid')
    ")->execute([
        ':uid'=> $userId,
        ':amt'=> $amount,
        ':cur'=> $currency,
        ':txn'=> $txnId
    ]);

    // Promote the user
    $pdo->prepare("UPDATE demon_users SET role_id = 8 WHERE id = :uid")
        ->execute([':uid'=> $userId]);

    $pdo->commit();
}

http_response_code(200);
