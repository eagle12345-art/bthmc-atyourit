<?php
session_start();
include "../config.php"; 

if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

// ============================================================
// HELPER: Generate kd otomatis (000001, 000002, dst)
// ============================================================
function generateKode($conn, $table, $field) {
    $q = mysqli_query($conn, "SELECT MAX(CAST($field AS UNSIGNED)) as max_id FROM $table");
    $d = mysqli_fetch_assoc($q);
    $next = ($d['max_id'] ?? 0) + 1;
    return str_pad($next, 6, "0", STR_PAD_LEFT);
}

// ============================================================
// TAMBAH USER BARU
// ============================================================
if (isset($_POST['save_user'])) {
    $nama     = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $level    = mysqli_real_escape_string($conn, $_POST['level']);
    $telepon  = mysqli_real_escape_string($conn, $_POST['telepon'] ?? '-');

    // Insert ke tabel users
    mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password, level) 
                         VALUES ('$nama', '$username', '$password', '$level')");

    // Jika Pelanggan → insert ke m_pelanggan
    if ($level == 'Pelanggan') {
        $kd = generateKode($conn, 'm_pelanggan', 'kd_pelanggan');
        mysqli_query($conn, "INSERT INTO m_pelanggan 
            (kd_pelanggan, nm_pelanggan, no_inet, alamat_pelanggan, email_pelanggan, username, password, tlp_pelanggan)
            VALUES ('$kd', '$nama', '-', '-', '-', '$username', '$password', '$telepon')");
    }

    // Jika Teknisi → insert ke m_teknisi
    if ($level == 'Teknisi') {
        $kd = generateKode($conn, 'm_teknisi', 'kd_teknisi');
        mysqli_query($conn, "INSERT INTO m_teknisi 
            (kd_teknisi, nm_teknisi, alamat_teknisi, email_teknisi, username, password, tlp_teknisi, status_teknisi)
            VALUES ('$kd', '$nama', '-', '-', '$username', '$password', '$telepon', 'Tidak Bekerja')");
    }

    echo "<script>alert('Success: User baru telah didaftarkan!'); window.location.href='data_user.php';</script>";
    exit();
}

// ============================================================
// UPDATE USER
// ============================================================
if (isset($_POST['update_user'])) {
    $id       = mysqli_real_escape_string($conn, $_POST['id_users']);
    $nama     = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $level    = mysqli_real_escape_string($conn, $_POST['level']);
    $telepon  = mysqli_real_escape_string($conn, $_POST['telepon'] ?? '');

    // Ambil data lama untuk tahu username lama
    $lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_users='$id'"));
    $username_lama = $lama['username'];
    $level_lama    = $lama['level'];

    // Update tabel users
    if (!empty($_POST['password'])) {
        $pass = mysqli_real_escape_string($conn, $_POST['password']);
        mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', username='$username', password='$pass', level='$level' WHERE id_users='$id'");
    } else {
        mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', username='$username', level='$level' WHERE id_users='$id'");
    }

    // Sync ke m_pelanggan jika level Pelanggan
    if ($level == 'Pelanggan') {
        $cek = mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM m_pelanggan WHERE username='$username_lama'"));
        if ($cek > 0) {
            // Update data yang sudah ada
            $pass_update = !empty($_POST['password']) ? ", password='" . mysqli_real_escape_string($conn, $_POST['password']) . "'" : "";
            $tlp_update  = !empty($telepon) ? ", tlp_pelanggan='$telepon'" : "";
            mysqli_query($conn, "UPDATE m_pelanggan SET nm_pelanggan='$nama', username='$username' $pass_update $tlp_update WHERE username='$username_lama'");
        } else {
            // Belum ada, insert baru (misal level baru diganti jadi Pelanggan)
            $kd = generateKode($conn, 'm_pelanggan', 'kd_pelanggan');
            $tlp = !empty($telepon) ? $telepon : '-';
            mysqli_query($conn, "INSERT INTO m_pelanggan 
                (kd_pelanggan, nm_pelanggan, no_inet, alamat_pelanggan, email_pelanggan, username, password, tlp_pelanggan)
                VALUES ('$kd', '$nama', '-', '-', '-', '$username', '" . ($lama['password']) . "', '$tlp')");
        }
        // Jika sebelumnya Teknisi, hapus dari m_teknisi
        if ($level_lama == 'Teknisi') {
            mysqli_query($conn, "DELETE FROM m_teknisi WHERE username='$username_lama'");
        }
    }

    // Sync ke m_teknisi jika level Teknisi
    if ($level == 'Teknisi') {
        $cek = mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM m_teknisi WHERE username='$username_lama'"));
        if ($cek > 0) {
            $pass_update = !empty($_POST['password']) ? ", password='" . mysqli_real_escape_string($conn, $_POST['password']) . "'" : "";
            $tlp_update  = !empty($telepon) ? ", tlp_teknisi='$telepon'" : "";
            mysqli_query($conn, "UPDATE m_teknisi SET nm_teknisi='$nama', username='$username' $pass_update $tlp_update WHERE username='$username_lama'");
        } else {
            $kd = generateKode($conn, 'm_teknisi', 'kd_teknisi');
            $tlp = !empty($telepon) ? $telepon : '-';
            mysqli_query($conn, "INSERT INTO m_teknisi 
                (kd_teknisi, nm_teknisi, alamat_teknisi, email_teknisi, username, password, tlp_teknisi, status_teknisi)
                VALUES ('$kd', '$nama', '-', '-', '$username', '" . ($lama['password']) . "', '$tlp', 'Tidak Bekerja')");
        }
        // Jika sebelumnya Pelanggan, hapus dari m_pelanggan
        if ($level_lama == 'Pelanggan') {
            mysqli_query($conn, "DELETE FROM m_pelanggan WHERE username='$username_lama'");
        }
    }

    // Jika level diganti jadi Admin, hapus dari tabel lama
    if ($level == 'Admin') {
        if ($level_lama == 'Pelanggan') mysqli_query($conn, "DELETE FROM m_pelanggan WHERE username='$username_lama'");
        if ($level_lama == 'Teknisi')   mysqli_query($conn, "DELETE FROM m_teknisi WHERE username='$username_lama'");
    }

    echo "<script>alert('Success: Data user berhasil diperbarui!'); window.location.href='data_user.php';</script>";
    exit();
}

// ============================================================
// HAPUS USER
// ============================================================
if (isset($_POST['confirm_delete'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_users']);

    // Ambil data user sebelum dihapus
    $user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_users='$id'"));
    
    if ($user_data) {
        $username_hapus = $user_data['username'];
        $level_hapus    = $user_data['level'];

        // Hapus dari tabel terkait
        if ($level_hapus == 'Pelanggan') {
            mysqli_query($conn, "DELETE FROM m_pelanggan WHERE username='$username_hapus'");
        }
        if ($level_hapus == 'Teknisi') {
            mysqli_query($conn, "DELETE FROM m_teknisi WHERE username='$username_hapus'");
        }

        // Hapus dari users
        mysqli_query($conn, "DELETE FROM users WHERE id_users='$id'");
    }

    header("location:data_user.php");
    exit();
}

// Jika akses langsung tanpa form
header("location:data_user.php");
exit();
?>