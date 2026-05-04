<?php
session_start();
include "../config.php";

if (!isset($_SESSION['level'])) {
    header("location:../index.php");
    exit();
}

$sql = "SELECT trx_laporan.*, 
               m_pelanggan.nm_pelanggan AS nm_associate, 
               m_layanan.nm_layanan,
               m_teknisi.nm_teknisi
        FROM trx_laporan 
        LEFT JOIN m_pelanggan ON trx_laporan.kd_pelanggan = m_pelanggan.kd_pelanggan 
        LEFT JOIN m_layanan ON trx_laporan.kd_layanan = m_layanan.kd_layanan
        LEFT JOIN m_teknisi ON trx_laporan.kd_teknisi = m_teknisi.kd_teknisi 
        ORDER BY tgl_lapor DESC";

$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate Complaints | Marriott IT Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../favicon.png"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        .content-wrapper { padding: 30px; flex: 1; }

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
        .status-selesai { background: #f0fff4; color: #2f855a; border: 1px solid #c6f6d5; }

        .btn-marriott {
            background: var(--marriott-red);
            color: white;
            font-weight: 700;
            border-radius: 8px;
            padding: 8px 20px;
            font-size: 0.85rem;
            border: none;
        }

        .btn-marriott:hover { background: #80001B; color: white; }

        /* Style Footer */
        footer {
            background: white;
            border-top: 1px solid #eee;
            padding: 20px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="page-title">
            <h4><i class="bi bi-ticket-perforated me-2"></i>Associate Complaints</h4>
        </div>
        <div>
            <a href="../dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 me-2">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <a href="laporan_cetak.php" class="btn btn-marriott">
                <i class="bi bi-printer me-2"></i> Print
            </a>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="card-custom">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Ticket Info</th>
                            <th>Associate Name</th>
                            <th>Issue Details</th>
                            <th>Technician</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($data = mysqli_fetch_assoc($query)) : 
                            $status = $data['status_lapor'];
                            $class = ($status == 'Selesai') ? 'status-selesai' : 'status-menunggu';
                        ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-dark" style="font-family: 'JetBrains Mono';"><?php echo $data['kd_lapor']; ?></span><br>
                                <small class="text-muted" style="font-size: 0.7rem;"><?php echo $data['tgl_lapor']; ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-uppercase"><?php echo $data['nm_associate'] ?? 'USER_UNKNOWN'; ?></div>
                                <small class="text-muted">Service ID: <?php echo $data['kd_layanan']; ?></small>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate small" title="<?php echo $data['keluhan']; ?>">
                                    <?php echo $data['keluhan']; ?>
                                </div>
                            </td>
                            <td>
                                <?php if($data['nm_teknisi']): ?>
                                    <span class="badge bg-light text-primary border fw-bold text-uppercase" style="font-size: 0.7rem;">
                                        <i class="bi bi-person-badge me-1"></i><?php echo $data['nm_teknisi']; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small italic"><i>Not Assigned</i></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge-status <?php echo $class; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="tunjuk_teknisi.php?id=<?php echo $data['unik']; ?>" class="btn btn-sm btn-outline-primary" title="Tunjuk Teknisi">
                                    <i class="bi bi-person-gear"></i>
                                </a>
                                <a href="hapus_keluhan.php?id=<?php echo $data['unik']; ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Hapus laporan ini?')" 
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Eagle - IT Intern Footer -->
    <footer>
        <div class="container text-center">
            <p class="text-muted small mb-1">Marriott International - IT Department Portal</p>
            <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.85rem; letter-spacing: 1px;">
                DEVELOPED BY EAGLE - IT INTERN &copy; 2026
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>