<?php
session_start();
include "../config.php";

// Proteksi: Pastikan user sudah login
if (!isset($_SESSION['level'])) {
    header("location:../index.php");
    exit();
}

// Cek apakah parameter 'id' ada di URL
if (isset($_GET['id'])) {
    // Menggunakan mysqli_real_escape_string untuk keamanan tambahan
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Proses Hapus Data berdasarkan kolom 'unik'
    $query_hapus = mysqli_query($conn, "DELETE FROM trx_laporan WHERE unik = '$id'");

    if ($query_hapus) {
        // Jika berhasil, tampilkan alert dan redirect balik
        echo "<script>
                alert('Complaint ticket has been successfully deleted.');
                window.location.href='data_keluhan.php';
              </script>";
    } else {
        // Jika gagal karena error database
        echo "<script>
                alert('Error: Could not delete data. " . mysqli_error($conn) . "');
                window.location.href='data_keluhan.php';
              </script>";
    }
} else {
    // Jika diakses langsung tanpa ID, lempar balik ke tabel
    header("location:data_keluhan.php");
}
?>