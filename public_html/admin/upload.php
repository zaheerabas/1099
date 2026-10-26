<?php
/* Image upload for the editor (returns JSON). */
require dirname(__DIR__) . '/app/bootstrap.php';
start_admin_session();
header('Content-Type: application/json');
$u = current_user();
if (!$u) { http_response_code(401); echo json_encode(['error' => 'Your session ended. Log in again.']); exit; }
if (!is_post()) { http_response_code(405); echo json_encode(['error' => 'Method not allowed.']); exit; }
csrf_check();
try {
    $m = store_upload($_FILES['image'] ?? [], (int)$u['id'], (string)($_POST['alt'] ?? ''));
    echo json_encode(['url' => url($m['path']), 'path' => $m['path'], 'width' => (int)$m['width'], 'height' => (int)$m['height']]);
} catch (RuntimeException $ex) {
    http_response_code(422);
    echo json_encode(['error' => $ex->getMessage()]);
}
