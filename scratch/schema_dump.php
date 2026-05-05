<?php
require_once __DIR__ . '/../Model/Database.php';
$db = Database::getInstance()->getConnection();
$r = $db->query('SHOW CREATE TABLE program')->fetch(PDO::FETCH_NUM);
echo $r[1];
?>
