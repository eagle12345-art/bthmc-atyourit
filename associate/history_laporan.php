<?php
session_start();
include "../config.php";

// 1. Ambil data session
$session_level = isset($_SESSION['level']) ? trim($_SESSION['level']) : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// 2. PROTEKSI HALAMAN (Disesuaikan dengan dashboard)
// Mengubah ke huruf kecil agar pengecekan akurat
$check = strtolower($session_level);

if ($check != "pelanggan" && $check != "associate") {
    // Jika BUKAN pelanggan/associate, lempar ke dashboard utama
    header("location:../dashboard.php");
    exit();
}

// 3. Query (Pastikan koneksi $conn dari config.php bekerja)
$query = mysqli_query($conn, "SELECT trx_laporan.*, m_layanan.nm_layanan 
                              FROM trx_laporan 
                              JOIN m_layanan ON trx_laporan.kd_layanan = m_layanan.kd_layanan
                              JOIN m_pelanggan ON trx_laporan.kd_pelanggan = m_pelanggan.kd_pelanggan
                              WHERE m_pelanggan.username = '$username' 
                              ORDER BY trx_laporan.tgl_lapor DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaint History | Marriott IT Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
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
        
        .content-wrapper { 
            padding: 30px; 
            flex: 1 0 auto;
        }

        .card-custom {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .table thead {
            background: #f8f9fa;
            color: #888;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table thead th { border: none; padding: 15px 20px; }
        .table tbody td { padding: 18px 20px; vertical-align: middle; border-color: #f0f0f0; }

        .badge-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-menunggu { background: #fff5f5; color: var(--marriott-red); border: 1px solid #ffebeb; }
        .status-proses { background: #fffbeb; color: #92400e; border: 1px solid #fef3c7; }
        .status-selesai { background: #f0fff4; color: #2f855a; border: 1px solid #c6f6d5; }

        .btn-marriott {
            background: var(--marriott-red);
            color: white;
            font-weight: 700;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.85rem;
            border: none;
            transition: 0.2s;
            text-decoration: none;
        }

        .btn-marriott:hover { background: #80001B; color: white; transform: translateY(-2px); }
        
        .ticket-code {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            color: var(--marriott-red);
        }

        footer {
            flex-shrink: 0;
            padding: 30px 0;
            background: white;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="page-title">
            <h4><i class="bi bi-clock-history me-2"></i>My Complaints</h4>
        </div>
        <div>
            <a href="../dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 me-2">
                <i class="bi bi-house-door me-1"></i> Back To Dashboard
            </a>
            <a href="lapor_gangguan.php" class="btn btn-marriott">
                <i class="bi bi-plus-lg me-2"></i> New Report
            </a>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Date</th>
                            <th>Service Category</th>
                            <th>Complaint Details</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($query) > 0) : ?>
                            <?php while($row = mysqli_fetch_assoc($query)): 
                                $status = $row['status_lapor'];
                                if ($status == 'Selesai') { $class = 'status-selesai'; }
                                elseif ($status == 'Proses') { $class = 'status-proses'; }
                                else { $class = 'status-menunggu'; }
                            ?>
                            <tr>
                                <td>
                                    <span class="ticket-code"><?php echo $row['kd_lapor']; ?></span>
                                </td>
                                <td>
                                    <small class="text-muted fw-bold">
                                        <?php echo date('d M Y', strtotime($row['tgl_lapor'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold text-uppercase small"><?php echo htmlspecialchars($row['nm_layanan']); ?></div>
                                </td>
                                <td style="max-width: 300px;">
                                    <div class="small text-secondary">
                                        <?php echo htmlspecialchars($row['keluhan']); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge-status <?php echo $class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    You haven't submitted any complaints yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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