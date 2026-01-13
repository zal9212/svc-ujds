<?php
$zip = new ZipArchive;
$res = $zip->open('tcpdf.zip');
if ($res === TRUE) {
    $zip->extractTo('tcpdf_extracted');
    $zip->close();
    echo "Extraction successful to 'tcpdf_extracted/'";
} else {
    echo "Extraction failed. Error code: " . $res;
}
