<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'pelanggan.xlsx';

try {
    $spreadsheet = IOFactory::load($file);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    echo "HEADERS (Row 0):\n";
    print_r($rows[0]);
    
    echo "\nSAMPLE DATA ROWS:\n";
    $count = 0;
    foreach ($rows as $idx => $row) {
        if ($idx === 0) continue;
        if (!empty(array_filter($row))) {
            echo "--- ROW $idx ---\n";
            foreach ($row as $k => $v) {
                if ($v !== null && $v !== '') {
                    echo "[$k] $v\n";
                }
            }
            $count++;
            if ($count >= 3) break;
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
