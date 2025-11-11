<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../koneksi.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Ambil data sekolah untuk hapus foto
    $query = "SELECT foto FROM sekolah WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $sekolah = mysqli_fetch_assoc($result);
        
        // Hapus foto jika ada
        if (!empty($sekolah['foto'])) {
            $foto_path = '../uploads/sekolah/' . $sekolah['foto'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
        
        // Hapus data dari database
        mysqli_query($conn, "DELETE FROM sekolah WHERE id = $id");
    }
}

header('Location: data-sekolah.php');
exit;
?>