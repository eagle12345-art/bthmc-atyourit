<?php
session_start();
include "../config.php"; 

// Access Control: Only for Teknisi
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Teknisi") {
    header("location:../index.php");
    exit();
}

// ============================================================
// FIX MASALAH 1: Ambil kd_teknisi berdasarkan username session
// Session hanya simpan 'username', jadi kita JOIN ke m_teknisi
// ============================================================
$username_session = mysqli_real_escape_string($conn, $_SESSION['username']);
$cek_teknisi = mysqli_query($conn, "SELECT kd_teknisi, nm_teknisi FROM m_teknisi WHERE username = '$username_session'");

if (mysqli_num_rows($cek_teknisi) == 0) {
    // Akun ini tidak terdaftar sebagai teknisi di m_teknisi
    echo "<script>alert('Error: Technician account not found in system. Please contact admin.'); window.location='../index.php';</script>";
    exit();
}
$data_teknisi_login = mysqli_fetch_assoc($cek_teknisi);
$kd_teknisi_login = $data_teknisi_login['kd_teknisi'];

// ============================================================
// FITUR BARU: Teknisi submit alasan pending
// ============================================================
if (isset($_POST['submit_pending'])) {
    $unik = mysqli_real_escape_string($conn, $_POST['unik']);
    $alasan = mysqli_real_escape_string($conn, $_POST['alasan_pending']);
    
    $query = mysqli_query($conn, "UPDATE trx_laporan SET 
        status_lapor = 'Pending',
        alasan_pending = '$alasan'
        WHERE unik = '$unik' AND kd_teknisi = '$kd_teknisi_login'");
    
    if ($query) {
        // Bebaskan status teknisi agar bisa di-assign ke pekerjaan lain
        mysqli_query($conn, "UPDATE m_teknisi SET status_teknisi = 'Tidak Bekerja' 
                             WHERE kd_teknisi = '$kd_teknisi_login'");
        echo "<script>alert('Task has been set to Pending. Admin has been notified.'); window.location='tugas_teknisi.php';</script>";
    }
    exit();
}

// Proses selesaikan pekerjaan
if (isset($_POST['submit_fix'])) {
    $unik = mysqli_real_escape_string($conn, $_POST['unik']);
    $tgl_perbaikan = mysqli_real_escape_string($conn, $_POST['tgl_perbaikan']);
    
    $query = mysqli_query($conn, "UPDATE trx_laporan SET 
        status_lapor = 'Selesai', 
        tgl_perbaikan = '$tgl_perbaikan',
        alasan_pending = NULL
        WHERE unik = '$unik' AND kd_teknisi = '$kd_teknisi_login'");
    
    if ($query) {
        // Bebaskan status teknisi setelah selesai
        mysqli_query($conn, "UPDATE m_teknisi SET status_teknisi = 'Tidak Bekerja' 
                             WHERE kd_teknisi = '$kd_teknisi_login'");
        echo "<script>alert('Report successfully completed!'); window.location='tugas_teknisi.php';</script>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Ops - IT Portal</title>
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --marriott-red: #A80023;
            --dark-tech: #0f1111;
        }
        body { 
            background-color: #f8f9fa; 
            background-image: linear-gradient(90deg, rgba(0,0,0,.02) 1px, transparent 1px), linear-gradient(rgba(0,0,0,.02) 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Plus Jakarta Sans', sans-serif; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-marriott { background: white; border-bottom: 2px solid var(--marriott-red); padding: 15px 0; }
        .task-card { background: white; border-radius: 20px; border: 1px solid rgba(0,0,0,0.05); transition: 0.3s; margin-bottom: 20px; }
        .status-badge { font-size: 0.7rem; font-weight: 800; letter-spacing: 1px; padding: 5px 15px; border-radius: 50px; background: #ffc107; color: #000; }
        .status-badge-pending { background: #dc3545; color: white; }
        .badge-done { background: #198754; color: white; }
        .btn-marriott { background: var(--marriott-red); color: white; border-radius: 12px; font-weight: 700; padding: 10px 25px; border: none; transition: 0.3s; }
        .btn-marriott:hover { background: #80001B; color: white; transform: translateY(-2px); }
        .btn-pending { background: #ffc107; color: #000; border-radius: 12px; font-weight: 700; padding: 10px 25px; border: none; transition: 0.3s; }
        .btn-pending:hover { background: #e0a800; color: #000; transform: translateY(-2px); }
        .history-section { background: white; border-radius: 25px; padding: 30px; border: 1px solid #eee; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .text-marriott { color: var(--marriott-red); }
        .content-wrapper { flex: 1 0 auto; }
        footer { flex-shrink: 0; padding: 30px 0; background: white; border-top: 1px solid #eee; }
        .modal-content { border-radius: 20px; border: none; }
    </style>
</head>
<body>

<div class="content-wrapper">
    <nav class="navbar navbar-marriott mb-5">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold" href="../dashboard.php">
                <i class="bi bi-arrow-left me-1"></i> BACK TO DASHBOARD
            </a>
            <span class="navbar-text fw-bold text-dark text-uppercase" style="letter-spacing: 1px;">
                Maintenance Operations — <?php echo htmlspecialchars($data_teknisi_login['nm_teknisi']); ?>
            </span>
        </div>
    </nav>

    <div class="container pb-5">
        <!-- ACTIVE MISSIONS SECTION -->
        <div class="row mb-5">
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Active Missions</h2>
                <p class="text-muted">Reports assigned to you requiring attention.</p>
            </div>

            <?php
            // ============================================================
            // FIX MASALAH 1: Filter berdasarkan kd_teknisi teknisi yang login
            // Tampilkan yang Proses atau Pending (milik teknisi ini)
            // ============================================================
            $active_tasks = mysqli_query($conn, "SELECT l.*, p.nm_pelanggan, lay.nm_layanan 
                                                FROM trx_laporan l
                                                LEFT JOIN m_pelanggan p ON l.kd_pelanggan = p.kd_pelanggan
                                                LEFT JOIN m_layanan lay ON l.kd_layanan = lay.kd_layanan
                                                WHERE l.kd_teknisi = '$kd_teknisi_login'
                                                  AND l.status_lapor IN ('Proses', 'Pending')
                                                ORDER BY l.unik DESC");
            
            if (mysqli_num_rows($active_tasks) > 0) {
                while ($d = mysqli_fetch_array($active_tasks)) {
                    $is_pending_task = ($d['status_lapor'] == 'Pending');
            ?>
            <div class="col-md-6 mb-4">
                <div class="card task-card p-4 shadow-sm <?php echo $is_pending_task ? 'border border-warning' : ''; ?>">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="status-badge <?php echo $is_pending_task ? 'status-badge-pending' : ''; ?>">
                            <i class="bi bi-<?php echo $is_pending_task ? 'pause-circle' : 'clock'; ?> me-1"></i>
                            <?php echo strtoupper($d['status_lapor']); ?>
                        </span>
                        <small class="text-muted fw-bold"><?php echo date('d M Y', strtotime($d['tgl_lapor'])); ?></small>
                    </div>
                    <h5 class="fw-bold mb-1">Ticket: <span class="text-marriott"><?php echo $d['kd_lapor']; ?></span></h5>
                    <p class="text-muted mb-3 small">Associate: <b><?php echo $d['nm_pelanggan']; ?></b> (<?php echo $d['nm_layanan']; ?>)</p>
                    <div class="bg-light p-3 rounded-3 mb-3" style="border-left: 4px solid var(--marriott-red);">
                        <p class="mb-0 small fst-italic">"<?php echo $d['keluhan']; ?>"</p>
                    </div>

                    <?php if ($is_pending_task && !empty($d['alasan_pending'])): ?>
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 mb-3" style="border-left: 4px solid #ffc107;">
                        <p class="mb-0 small"><b>Pending Reason:</b> <?php echo htmlspecialchars($d['alasan_pending']); ?></p>
                        <p class="mb-0 small text-muted mt-1"><i class="bi bi-info-circle me-1"></i>Waiting for admin to reassign.</p>
                    </div>
                    <?php endif; ?>

                    <div class="row g-2">
                        <!-- Tombol Mark as Fixed -->
                        <div class="col-<?php echo $is_pending_task ? '12' : '6'; ?>">
                            <button type="button" class="btn btn-marriott w-100" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalFix<?php echo $d['unik']; ?>">
                                <i class="bi bi-check-circle me-2"></i> MARK AS FIXED
                            </button>
                        </div>

                        <!-- Tombol Pending hanya tampil jika status masih Proses -->
                        <?php if (!$is_pending_task): ?>
                        <div class="col-6">
                            <button type="button" class="btn btn-pending w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPending<?php echo $d['unik']; ?>">
                                <i class="bi bi-pause-circle me-2"></i> SET PENDING
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- MODAL: MARK AS FIXED -->
            <div class="modal fade" id="modalFix<?php echo $d['unik']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg">
                        <form method="POST">
                            <div class="modal-body p-5 text-center">
                                <i class="bi bi-tools text-marriott mb-3" style="font-size: 3rem;"></i>
                                <h4 class="fw-bold mb-3">Task Completion</h4>
                                <input type="hidden" name="unik" value="<?php echo $d['unik']; ?>">
                                <div class="text-start mb-4">
                                    <label class="small fw-bold mb-2 d-block text-uppercase">Completion Date:</label>
                                    <input type="date" name="tgl_perbaikan" class="form-control form-control-lg" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-light w-100 py-3 fw-bold" data-bs-dismiss="modal">CANCEL</button>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" name="submit_fix" class="btn btn-marriott w-100 py-3">SAVE CHANGES</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL: SET PENDING (hanya tampil jika status Proses) -->
            <?php if (!$is_pending_task): ?>
            <div class="modal fade" id="modalPending<?php echo $d['unik']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-lg">
                        <form method="POST">
                            <div class="modal-body p-5 text-center">
                                <i class="bi bi-pause-circle text-warning mb-3" style="font-size: 3rem;"></i>
                                <h4 class="fw-bold mb-1">Set Task to Pending</h4>
                                <p class="text-muted small mb-4">Admin will be notified and may reassign this task.</p>
                                <input type="hidden" name="unik" value="<?php echo $d['unik']; ?>">
                                <div class="text-start mb-4">
                                    <label class="small fw-bold mb-2 d-block text-uppercase">Reason for Pending <span class="text-danger">*</span></label>
                                    <textarea name="alasan_pending" class="form-control" rows="3" 
                                              placeholder="e.g. Waiting for spare parts, need specialist help..." 
                                              required></textarea>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button" class="btn btn-light w-100 py-3 fw-bold" data-bs-dismiss="modal">CANCEL</button>
                                    </div>
                                    <div class="col-6">
                                        <button type="submit" name="submit_pending" class="btn btn-pending w-100 py-3">CONFIRM PENDING</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php 
                }
            } else {
                echo '<div class="col-12 text-center py-5 shadow-sm bg-white rounded-4">
                        <i class="bi bi-check-circle text-success" style="font-size:2.5rem;"></i>
                        <p class="text-muted mb-0 fw-bold mt-3">No active missions. You\'re all clear!</p>
                      </div>';
            }
            ?>
        </div>

        <hr class="my-5" style="opacity: 0.1;">

        <!-- JOB HISTORY SECTION -->
        <div class="history-section mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-0">Job History</h3>
                    <p class="text-muted small">Your completed maintenance tasks.</p>
                </div>
                <span class="badge bg-dark rounded-pill px-3 py-2">COMPLETED TASKS</span>
            </div>

            <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 px-3">Ticket</th>
                            <th class="border-0">Service Category</th>
                            <th class="border-0">Associate</th>
                            <th class="border-0">Reported Date</th>
                            <th class="border-0">Resolved Date</th>
                            <th class="border-0 text-center">Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // History hanya milik teknisi yang login
                        $history = mysqli_query($conn, "SELECT l.*, p.nm_pelanggan, lay.nm_layanan 
                                                       FROM trx_laporan l
                                                       LEFT JOIN m_pelanggan p ON l.kd_pelanggan = p.kd_pelanggan
                                                       LEFT JOIN m_layanan lay ON l.kd_layanan = lay.kd_layanan
                                                       WHERE l.status_lapor = 'Selesai' 
                                                         AND l.kd_teknisi = '$kd_teknisi_login'
                                                       ORDER BY tgl_perbaikan DESC LIMIT 10");
                        
                        if (mysqli_num_rows($history) > 0) {
                            while ($h = mysqli_fetch_array($history)) {
                        ?>
                        <tr>
                            <td class="px-3 fw-bold text-marriott"><?php echo $h['kd_lapor']; ?></td>
                            <td><span class="small"><?php echo $h['nm_layanan']; ?></span></td>
                            <td class="small fw-semibold"><?php echo $h['nm_pelanggan']; ?></td>
                            <td class="text-muted small"><?php echo date('d M Y', strtotime($h['tgl_lapor'])); ?></td>
                            <td class="fw-bold small"><?php echo date('d M Y', strtotime($h['tgl_perbaikan'])); ?></td>
                            <td class="text-center">
                                <span class="status-badge badge-done"><i class="bi bi-check-all me-1"></i>CLOSED</span>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="6" class="text-center py-4 text-muted">No task history found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="text-center">
    <div class="container">
        <p class="text-muted small mb-1">Batam Marriott Hotel Harbour Bay — IT Department</p>
        <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.85rem; letter-spacing: 1.5px; text-transform: uppercase;">
            Developed by <span style="font-weight: 800;">Eagle</span> — IT Intern © 2026
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>