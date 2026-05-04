<?php
session_start();
include "../config.php";

// 1. AMBIL DATA SESSION
// Gunakan trim untuk menghapus spasi tak terlihat yang sering merusak pengecekan
$session_level = isset($_SESSION['level']) ? trim($_SESSION['level']) : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// 2. PROTEKSI HALAMAN (SINKRON DENGAN DASHBOARD)
// Kita ubah ke huruf kecil semua agar pengecekan "pelanggan" vs "Pelanggan" tetap lolos
$check = strtolower($session_level);

if ($check != "pelanggan" && $check != "associate") {
    // Jika level tidak cocok, arahkan kembali ke dashboard utama
    header("location:../dashboard.php");
    exit();
}

// 3. AMBIL DATA ASSOCIATE DARI DATABASE
$get_user = mysqli_query($conn, "SELECT * FROM m_pelanggan WHERE username='$username'");
$data_plg = mysqli_fetch_assoc($get_user);

// Cek jika data pelanggan tidak ada di table m_pelanggan
if (!$data_plg) {
    echo "<script>alert('Error: Data Associate tidak ditemukan di sistem.'); window.location.href='../dashboard.php';</script>";
    exit();
}

$kd_pelanggan = $data_plg['kd_pelanggan'];

// 4. PROSES SIMPAN LAPORAN
if (isset($_POST['kirim_lapor'])) {
    $tgl = date('Y-m-d');
    $layanan = $_POST['kd_layanan'];
    $keluhan = mysqli_real_escape_string($conn, $_POST['keluhan']);
    
    // Generate Kode Lapor Otomatis (Contoh: LP-000001)
    $query_max = mysqli_query($conn, "SELECT max(unik) as max_id FROM trx_laporan");
    $data_max = mysqli_fetch_assoc($query_max);
    $next_id = ($data_max['max_id'] ?? 0) + 1;
    $kd_lapor = "LP-" . str_pad($next_id, 6, "0", STR_PAD_LEFT);

    $simpan = mysqli_query($conn, "INSERT INTO trx_laporan (kd_lapor, tgl_lapor, kd_pelanggan, kd_layanan, keluhan, status_lapor) 
                                   VALUES ('$kd_lapor', '$tgl', '$kd_pelanggan', '$layanan', '$keluhan', 'Menunggu')");

    if ($simpan) {
        echo "<script>alert('Report submitted successfully!'); window.location.href='history_laporan.php';</script>";
    } else {
        echo "<script>alert('Failed to submit report. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Report | Marriott IT Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root {
            --marriott-red: #A80023;
            --dark-tech: #0f1111;
        }

        body {
            background-color: #f4f7f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h4 { margin: 0; font-weight: 800; color: var(--dark-tech); }

        .form-container {
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            flex: 1 0 auto;
        }

        .card-lapor {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            height: fit-content;
        }

        .card-header-red {
            background: var(--marriott-red);
            padding: 25px;
            color: white;
            text-align: center;
        }

        .card-body-content { padding: 35px; }

        .form-label {
            font-weight: 700;
            font-size: 0.8rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px;
            border: 2px solid #eee;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--marriott-red);
            box-shadow: none;
        }

        .form-control[readonly] {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .btn-marriott {
            background: var(--marriott-red);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 14px;
            border: none;
            transition: 0.3s;
        }

        .btn-marriott:hover {
            background: #80001B;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 0, 35, 0.3);
        }

        .btn-cancel {
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            border: 2px solid #eee;
            background: #fff;
            color: #666;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .btn-cancel:hover {
            background: #f8f9fa;
            color: var(--dark-tech);
        }

        .instruction-text {
            background: #fff5f5;
            color: var(--marriott-red);
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            border-left: 4px solid var(--marriott-red);
        }

        .btn-history {
            text-align: center;
            display: block;
            margin-top: 20px;
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-history:hover { color: var(--marriott-red); }

        footer {
            flex-shrink: 0;
            padding: 30px 0;
            background: white;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <div class="top-bar">
        <div class="page-title">
            <h4><i class="bi bi-megaphone me-2"></i>IT Helpdesk</h4>
        </div>
        <div class="d-flex align-items-center">
            <a href="../dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 me-2">
                <i class="bi bi-grid-1x2 me-1"></i> Dashboard
            </a>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>

    <div class="form-container">
        <div class="card-lapor">
            <div class="card-header-red">
                <h5 class="mb-0 fw-bold">CREATE NEW ISSUE</h5>
                <p class="small mb-0 opacity-75">Batam Marriott Hotel Harbour Bay</p>
            </div>
            
            <div class="card-body-content">
                <div class="instruction-text">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Please describe the technical issue you are experiencing clearly.
                </div>

                <form method="POST">
                    <!-- Associate Identity -->
                    <div class="mb-3">
                        <label class="form-label">Associate Name</label>
                        <input type="text" class="form-control" value="<?php echo strtoupper($data_plg['nm_pelanggan'] ?? 'USER'); ?>" readonly>
                    </div>

                    <!-- Service Selection -->
                    <div class="mb-3">
                        <label class="form-label">Service Category</label>
                        <select name="kd_layanan" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            <?php 
                            $lanyanan_q = mysqli_query($conn, "SELECT * FROM m_layanan");
                            while($l = mysqli_fetch_array($lanyanan_q)){
                                echo "<option value='$l[kd_layanan]'>$l[nm_layanan]</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Issue Details -->
                    <div class="mb-4">
                        <label class="form-label">Issue Details</label>
                        <textarea name="keluhan" class="form-control" rows="4" placeholder="e.g. Printer offline on 2nd floor, cannot access Opera PMS, etc." required></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row g-2">
                        <div class="col-md-8">
                            <button type="submit" name="kirim_lapor" class="btn btn-marriott w-100">
                                <i class="bi bi-send-fill me-2"></i> SUBMIT TICKET
                            </button>
                        </div>
                        <div class="col-md-4">
                            <a href="../dashboard.php" class="btn-cancel">
                                CANCEL
                            </a>
                        </div>
                    </div>
                    
                    <a href="history_laporan.php" class="btn-history">
                        <i class="bi bi-journal-text me-1"></i> View My Report History
                    </a>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="text-muted small mb-1">Batam Marriott Hotel Harbour Bay — IT Department</p>
            <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.85rem; letter-spacing: 1.5px; text-transform: uppercase;">
                Developed by Eagle — IT Intern © 2026
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>