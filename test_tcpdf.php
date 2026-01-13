<?php
require_once __DIR__ . '/vendor/autoload.php';

try {
    if (class_exists('TCPDF')) {
        echo "SUCCESS: TCPDF class is loaded.\n";
        $pdf = new TCPDF();
        echo "SUCCESS: Instance created.\n";
    } else {
        echo "FAILURE: TCPDF class not found.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
