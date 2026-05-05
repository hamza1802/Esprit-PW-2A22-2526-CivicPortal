<?php
require_once __DIR__ . '/../Model/AppModel.php';
AppModel::init();
$res = AppModel::getPrograms();
$json = json_encode(['success' => true, 'data' => $res]);
if ($json === false) {
    echo 'JSON ERROR: ' . json_last_error_msg();
} else {
    echo 'JSON OK';
}
?>
