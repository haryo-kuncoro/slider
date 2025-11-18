<?php
require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('wisudawan_sample');

// Header kolom
$headers = [
    'A1' => 'urutan',
    'B1' => 'nirm',
    'C1' => 'nama',
    'D1' => 'ortu_laki',
    'E1' => 'ortu_perempuan',
    'F1' => 'tmp_tgl_lahir',
    'G1' => 'asal_sekolah',
    'H1' => 'alamat',
    'I1' => 'ipk',
    'J1' => 'judul',
    'K1' => 'keterangan',
    'L1' => 'prodi',
    'M1' => 'gelombang',
];

// Isi header
foreach ($headers as $cell => $text) {
    $sheet->setCellValue($cell, $text);
}

// Tambahkan contoh 2 baris data
$data = [
    ['1', '202312345', 'Budi Santoso', 'Slamet', 'Siti', 'Medan / 10 Februari 2002', 'SMAN 1 Medan', 'Jl. Mawar 12', '3.55', 'Analisis Sistem Informasi', 'Sangat Memuaskan', 'Teknik Informatika', '1'],
    ['2', '202312346', 'Ani Lestari', 'Bambang', 'Sri', 'Binjai / 11 Maret 2001', 'SMAN 2 Binjai', 'Jl. Kenanga 5', '3.67', 'Optimasi Jaringan', 'Cumlaude', 'Sistem Informasi', '1'],
];

$startRow = 2;
foreach ($data as $row) {
    $col = 'A';
    foreach ($row as $value) {
        $sheet->setCellValue($col . $startRow, $value);
        $col++;
    }
    $startRow++;
}

// Output ke browser (download)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="sample_wisudawan.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;