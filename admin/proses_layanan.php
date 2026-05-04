<?php
session_start();
include "../config.php"; // Keluar satu tingkat folder untuk memanggil config

// Proteksi: Hanya Admin yang bisa memproses
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

if (isset($_POST['simpan'])) {
    // Ambil data dari modal Tambah Layanan
    $nm_layanan  = mysqli_real_escape_string($conn, $_POST['nm_layanan']);
    $ket_layanan = mysqli_real_escape_string($conn, $_POST['ket_layanan']);

    // Query INSERT ke tabel m_layanan
    $query = "INSERT INTO m_layanan (nm_layanan, ket_layanan) VALUES ('$nm_layanan', '$ket_layanan')";

    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Layanan baru berhasil ditambahkan!');
                window.location.href='data_layanan.php';
              </script>";
    } else {
        echo "Gagal menambahkan layanan: " . mysqli_error($conn);
    }
} else {
    // Jika akses langsung tanpa submit form
    header("location:data_layanan.php");
}
?>