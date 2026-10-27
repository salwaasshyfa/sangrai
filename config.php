<?php
// Konfigurasi & data konten landing page
const SITE_NAME = 'Sangrai';
const SITE_TAGLINE = 'Kopi sangrai kecil, dikirim minggu ini.';

function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function rupiah(int $n): string { return 'Rp' . number_format($n, 0, ',', '.'); }

$seedProducts = [
    ['name' => 'Kintamani Natural', 'origin' => 'Bali',        'roast' => 2, 'notes' => 'Jeruk, madu, teh hitam', 'price' => 89000],
    ['name' => 'Gayo Honey',        'origin' => 'Aceh',        'roast' => 3, 'notes' => 'Karamel, rempah, cokelat susu', 'price' => 95000],
    ['name' => 'Flores Bajawa',     'origin' => 'NTT',         'roast' => 4, 'notes' => 'Cokelat pekat, kacang panggang', 'price' => 85000],
];
$roastLabels = [1 => 'Light', 2 => 'Medium light', 3 => 'Medium', 4 => 'Medium dark', 5 => 'Dark'];

$steps = [
    ['Pilih biji', 'Kami beli langsung dari petani dan cantumkan asal tiap kantong.'],
    ['Sangrai kecil', 'Satu batch maksimal 5 kg, disangrai setelah pesanan masuk.'],
    ['Kirim segar', 'Dikemas dengan katup aroma dan dikirim di hari yang sama.'],
];
