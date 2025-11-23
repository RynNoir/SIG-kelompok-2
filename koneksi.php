<?php
/**
 * Koneksi Database
 * Sistem Zonasi SMP Padang
 */

// Konfigurasi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "zonasi_smp";

// Koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

// Function hitung jarak (Haversine)
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000;
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($latFrom) * cos($latTo) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $earthRadius * $c;
}

// Function format jarak
function formatJarak($meter) {
    if ($meter < 1000) {
        return round($meter) . ' m';
    }
    return number_format($meter / 1000, 2, ',', '.') . ' km';
}
?>