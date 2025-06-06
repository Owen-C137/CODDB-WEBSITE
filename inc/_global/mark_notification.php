<?php
// inc/_global/mark_notification.php

// Turn on full PHP error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: application/json');

try {
    // 1) Load our global config (adjust path if needed)
    require __DIR__ . '/config.php';
    session_start();

    // 2) Must be logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    $userId = $_SESSION['user_id'];

    // 3) action must be provided
    if (empty($_POST['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing action']);
        exit;
    }

    $action = $_POST['action'];

    // 4a) If marking a single notification
    if ($action === 'single') {
        if (empty($_POST['notif_id']) || !ctype_digit((string)$_POST['notif_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid notif_id']);
            exit;
        }
        $nid = (int)$_POST['notif_id'];

        $stmt = $pdo->prepare("
            UPDATE `demon_notifications`
            SET is_read = 1
            WHERE id = :nid
              AND user_id = :uid
        ");
        $stmt->execute([
            ':nid' => $nid,
            ':uid' => $userId
        ]);

        echo json_encode(['success' => true, 'updated_id' => $nid]);
        exit;
    }

    // 4b) If marking all unread as read
    if ($action === 'all') {
        $stmt = $pdo->prepare("
            UPDATE `demon_notifications`
            SET is_read = 1
            WHERE user_id = :uid
              AND is_read = 0
        ");
        $stmt->execute([':uid' => $userId]);

        echo json_encode(['success' => true, 'updated_all' => true]);
        exit;
    }

    // 5) If action is neither “single” nor “all”
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (\PDOException $e) {
    http_response_code(500);
    // Return the actual PDO error message for debugging
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
} catch (\Throwable $e) {
    http_response_code(500);
    // Return the raw exception message for debugging
    echo json_encode(['success' => false, 'error' => 'Server exception: ' . $e->getMessage()]);
    exit;
}
