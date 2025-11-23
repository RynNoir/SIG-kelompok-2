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


?>