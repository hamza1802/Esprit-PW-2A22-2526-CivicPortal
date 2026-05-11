<?php
require_once __DIR__ . '/Model/Database.php';
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare('DESCRIBE users');
$stmt->execute();
print_r($stmt->fetchAll());
?>
