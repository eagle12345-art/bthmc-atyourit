<?php
session_start();
include "../config.php"; 

// Proteksi Admin
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

// Ambil ID laporan dari URL
if (!isset($_GET['id'])) {
    header("location:data_keluhan.php");
    exit();
}
$id_laporan = mysqli_real_escape_string($conn, $_GET['id']);

// Jika tombol Simpan ditekan
if (isset($_POST['assign'])) {
    $kd_teknisi = mysqli_real_escape_string($conn, $_POST['kd_teknisi']);
    
    // Update status laporan menjadi 'Proses' dan tugaskan teknisi
    $update = mysqli_query($conn, "UPDATE trx_laporan SET 
                                   kd_teknisi = '$kd_teknisi', 
                                   status_lapor = 'Proses',
                                   alasan_pending = NULL
                                   WHERE unik = '$id_laporan'");

    if ($update) {
        // Update status teknisi menjadi 'Sedang Bekerja'
        mysqli_query($conn, "UPDATE m_teknisi SET status_teknisi = 'Sedang Bekerja' 
                             WHERE kd_teknisi = '$kd_teknisi'");

        echo "<script>
                alert('Success: Technician has been assigned!');
                window.location.href='data_keluhan.php';
              </script>";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

// Ambil detail laporan
$detail = mysqli_query($conn, "SELECT trx_laporan.*, m_pelanggan.nm_pelanggan AS nm_associate 
                               FROM trx_laporan 
                               JOIN m_pelanggan ON trx_laporan.kd_pelanggan = m_pelanggan.kd_pelanggan 
                               WHERE trx_laporan.unik = '$id_laporan'");
$data_lapor = mysqli_fetch_assoc($detail);

if (!$data_lapor) {
    echo "<script>alert('Error: Ticket not found!'); window.location.href='data_keluhan.php';</script>";
    exit();
}

// ============================================================
// FIX MASALAH 2 & 3:
// - JOIN dengan users untuk pastikan akun teknisi masih aktif
// - WHERE status_teknisi = 'Tidak Bekerja' untuk filter yang sedang bekerja
// - Jika laporan ini sedang Pending, teknisi yang sedang handle laporan ini
//   juga ditampilkan (karena statusnya bisa di-reassign)
// ============================================================
$is_pending = ($data_lapor['status_lapor'] == 'Pending');

$teknisi_list = mysqli_query($conn, "
    SELECT m_teknisi.* 
    FROM m_teknisi
    INNER JOIN users ON m_teknisi.username = users.username
    WHERE users.level = 'Teknisi'
      AND (
          m_teknisi.status_teknisi = 'Tidak Bekerja'
          " . ($is_pending ? "OR m_teknisi.kd_teknisi = '" . mysqli_real_escape_string($conn, $data_lapor['kd_teknisi']) . "'" : "") . "
      )
    ORDER BY m_teknisi.nm_teknisi ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Technician | Marriott IT Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../favicon.png">
    
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
        .content-wrapper { 
            padding: 40px 20px; 
            display: flex;
            flex: 1;
            justify-content: center;
            align-items: center;
        }
        .card-assign {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 550px;
            overflow: hidden;
        }
        .card-header-red {
            background: var(--marriott-red);
            padding: 25px;
            color: white;
            text-align: center;
        }
        .card-body-content { padding: 30px; }
        .info-box {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid var(--marriott-red);
        }
        .pending-box {
            background: #fff8e1;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .ticket-id {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            color: var(--marriott-red);
        }
        .form-label { font-weight: 700; font-size: 0.9rem; color: #555; margin-bottom: 10px; }
        .form-select-lg {
            border-radius: 12px;
            font-size: 1rem;
            padding: 12px;
            border: 2px solid #eee;
            transition: 0.2s;
        }
        .form-select-lg:focus { border-color: var(--marriott-red); box-shadow: none; }
        .btn-marriott {
            background: var(--marriott-red);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 14px;
            width: 100%;
            border: none;
            transition: 0.3s;
        }
        .btn-marriott:hover {
            background: #80001B;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 0, 35, 0.3);
        }
        .btn-cancel {
            text-align: center;
            display: block;
            margin-top: 15px;
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .btn-cancel:hover { color: var(--dark-tech); }
        footer { padding: 20px 0; background: transparent; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="page-title">
            <h4><i class="bi bi-person-badge me-2"></i>Technician Assignment</h4>
        </div>
        <a href="data_keluhan.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Records
        </a>
    </div>

    <div class="content-wrapper">
        <div class="card-assign">
            <div class="card-header-red">
                <h5 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 1px;">
                    <?php echo $is_pending ? 'Reassign Pending Ticket' : 'Update Ticket Status'; ?>
                </h5>
            </div>
            
            <div class="card-body-content">
                <!-- Detail Info Box -->
                <div class="info-box">
                    <div class="row mb-2">
                        <div class="col-4 text-muted small fw-bold">Ticket ID</div>
                        <div class="col-8 ticket-id"><?php echo $data_lapor['kd_lapor']; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted small fw-bold">Associate</div>
                        <div class="col-8 fw-bold text-uppercase"><?php echo htmlspecialchars($data_lapor['nm_associate']); ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4 text-muted small fw-bold">Issue</div>
                        <div class="col-8 small fst-italic text-secondary">"<?php echo htmlspecialchars($data_lapor['keluhan']); ?>"</div>
                    </div>
                    <div class="row">
                        <div class="col-4 text-muted small fw-bold">Status</div>
                        <div class="col-8">
                            <?php
                            $status = $data_lapor['status_lapor'];
                            $badge_class = 'bg-secondary';
                            if ($status == 'Proses') $badge_class = 'bg-warning text-dark';
                            elseif ($status == 'Selesai') $badge_class = 'bg-success';
                            elseif ($status == 'Pending') $badge_class = 'bg-danger';
                            elseif ($status == 'Menunggu') $badge_class = 'bg-info text-dark';
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo strtoupper($status); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Kotak alasan pending jika status Pending -->
                <?php if ($is_pending && !empty($data_lapor['alasan_pending'])): ?>
                <div class="pending-box">
                    <div class="small fw-bold text-warning mb-1"><i class="bi bi-exclamation-triangle me-1"></i>PENDING REASON FROM TECHNICIAN:</div>
                    <div class="small"><?php echo htmlspecialchars($data_lapor['alasan_pending']); ?></div>
                </div>
                <?php endif; ?>

                <!-- Assignment Form -->
                <form action="" method="POST">
                    <div class="mb-4">
                        <label class="form-label">SELECT TECHNICIAN ON DUTY</label>
                        
                        <?php if (mysqli_num_rows($teknisi_list) == 0): ?>
                            <div class="alert alert-warning rounded-3 small">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                <strong>No technicians available.</strong> All active technicians are currently on duty. Please wait until one completes their task.
                            </div>
                        <?php else: ?>
                            <select name="kd_teknisi" class="form-select form-select-lg" required>
                                <option value="">-- Choose Technician --</option>
                                <?php while ($t = mysqli_fetch_assoc($teknisi_list)) : ?>
                                    <option value="<?php echo $t['kd_teknisi']; ?>">
                                        <?php echo strtoupper($t['nm_teknisi']); ?> (<?php echo $t['kd_teknisi']; ?>)
                                        <?php if ($is_pending && $t['kd_teknisi'] == $data_lapor['kd_teknisi']): ?>
                                            — Previously Assigned (Pending)
                                        <?php endif; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <div class="mt-3 p-2 rounded bg-light border">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i> 
                                    <?php if ($is_pending): ?>
                                        Reassigning will update status back to <span class="badge bg-warning text-dark">PROSES</span>.
                                    <?php else: ?>
                                        Assigning a technician will update status to <span class="badge bg-warning text-dark">PROSES</span>.
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (mysqli_num_rows($teknisi_list) > 0 || true): ?>
                        <?php
                        // Reset pointer supaya bisa cek jumlah lagi
                        // Tombol tetap muncul hanya jika ada teknisi
                        $teknisi_count_check = mysqli_query($conn, "
                            SELECT COUNT(*) as cnt FROM m_teknisi
                            INNER JOIN users ON m_teknisi.username = users.username
                            WHERE users.level = 'Teknisi'
                              AND (
                                  m_teknisi.status_teknisi = 'Tidak Bekerja'
                                  " . ($is_pending ? "OR m_teknisi.kd_teknisi = '" . mysqli_real_escape_string($conn, $data_lapor['kd_teknisi']) . "'" : "") . "
                              )
                        ");
                        $cnt = mysqli_fetch_assoc($teknisi_count_check);
                        if ($cnt['cnt'] > 0):
                        ?>
                        <button type="submit" name="assign" class="btn btn-marriott">
                            <i class="bi bi-check-circle me-2"></i> 
                            <?php echo $is_pending ? 'REASSIGN TECHNICIAN' : 'CONFIRM ASSIGNMENT'; ?>
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <a href="data_keluhan.php" class="btn-cancel">Discard Changes</a>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="text-muted small mb-1">Batam Marriott Hotel Harbour Bay - IT Department</p>
            <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.8rem; letter-spacing: 1px;">
                DEVELOPED BY EAGLE - IT INTERN &copy; 2026
            </p>
        </div>
    </footer>

</body>
</html>