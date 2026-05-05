<?php
require_once __DIR__ . '/../Model/AppModel.php';
AppModel::init();
$res = AppModel::getPrograms();
print_r($res);
?>
