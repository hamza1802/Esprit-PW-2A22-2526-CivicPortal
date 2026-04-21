<?php
$file = 'Controller/MainController.php';
$content = file_get_contents($file);

// Insert complaints cases right before the Transport section
$needle = '            // --- Transport & Route CRUD ---';
$insert = '            // --- Complaints ---
            case \'get_complaints\':
                return AppModel::getRequests();
            case \'add_complaint\':
                return AppModel::addRequest($data[\'subject\'] ?? $data[\'title\'] ?? \'Complaint\', $data[\'userId\'] ?? 1);

            ';

$content = str_replace($needle, $insert . $needle, $content);
file_put_contents($file, $content);

// Verify syntax
exec('d:\\xampp\\xampp\\php\\php.exe -l ' . $file, $out, $code);
echo implode("\n", $out) . "\n";
echo "Exit code: $code\n";
