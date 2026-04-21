<?php
$_POST['action'] = 'add_program';
$_POST['title'] = 'Test Program For Save';
$_POST['description'] = 'This is a test description of a program that is twenty chars';
$_POST['category'] = 'Sports';
$_POST['capacity'] = 10;
$_POST['location'] = 'Park Central';
$_POST['status'] = 'active';

ob_start();
require __DIR__ . '/../Verification.php';
echo ob_get_clean();
?>
