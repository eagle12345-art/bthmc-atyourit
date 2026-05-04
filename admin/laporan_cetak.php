<?php
session_start();
include "../config.php"; 

// Proteksi: Hanya Admin yang bisa akses
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

/**
 * QUERY FINAL:
 * Mengambil data laporan dengan JOIN:
 * 1. m_pelanggan (Associate)
 * 2. m_layanan (Service)
 * 3. m_teknisi (Technician) -> Menggunakan kd_teknisi sesuai struktur DB Anda
 */
$query = "SELECT trx_laporan.*, 
                 m_pelanggan.nm_pelanggan, 
                 m_layanan.nm_layanan,
                 m_teknisi.nm_teknisi
          FROM trx_laporan 
          LEFT JOIN m_pelanggan ON trx_laporan.kd_pelanggan = m_pelanggan.kd_pelanggan 
          LEFT JOIN m_layanan ON trx_laporan.kd_layanan = m_layanan.kd_layanan 
          LEFT JOIN m_teknisi ON trx_laporan.kd_teknisi = m_teknisi.kd_teknisi
          ORDER BY trx_laporan.tgl_lapor DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT_Complaint_Report_<?php echo date('Ymd'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
	<link rel="icon" type="image/png" href="../favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    
    <style>
        body { 
            padding: 40px; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #333;
            background-color: #fff;
        }

        /* Header Logo */
        .header-report {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #A80023;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logo-marriott { height: 45px; }
        .logo-it { height: 40px; filter: grayscale(1); opacity: 0.7; }
        .divider-v { width: 2px; height: 35px; background: #ddd; }

        .report-title { text-align: right; }
        .report-title h2 { 
            margin: 0; 
            font-weight: 800; 
            color: #A80023;
            font-size: 1.4rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .report-title p { margin: 0; font-size: 0.8rem; color: #666; }

        /* Table Styling */
        .table { font-size: 0.75rem; border-color: #dee2e6; }
        .table thead {
            background-color: #f8f9fa !important;
            border-top: 2px solid #333;
        }
        .table thead th { 
            text-transform: uppercase; 
            font-size: 0.65rem; 
            letter-spacing: 1px; 
            padding: 10px;
            vertical-align: middle;
            text-align: center;
        }

        .tech-font { font-family: 'JetBrains Mono', monospace; font-weight: bold; color: #A80023; }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            background: #f0f0f0;
            font-size: 0.65rem;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #ddd;
        }

        /* Footer Signature */
        .signature-area {
            margin-top: 40px;
            font-size: 0.85rem;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .table thead { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
            .header-report { border-bottom: 3px solid #A80023 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- Header Area -->
    <div class="header-report">
        <div class="logo-box">
            <img src="../marriott-logo.png" class="logo-marriott" alt="Marriott Logo">
            <div class="divider-v"></div>
            <img src="../logofix.png" class="logo-it" alt="IT Dept Logo">
        </div>
        <div class="report-title">
            <h2>IT COMPLAINT REPORT</h2>
            <p>Batam Marriott Hotel Harbour Bay</p>
            <p class="small text-muted">Internal Tech Infrastructure & Operations</p>
        </div>
    </div>

    <!-- Metadata Report -->
    <div class="mb-4">
        <table class="table table-sm table-borderless w-auto" style="font-size: 0.75rem;">
            <tr>
                <td class="text-muted">Report Generated</td>
                <td class="px-2">:</td>
                <td><strong><?php echo date('d M Y / H:i'); ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Document Status</td>
                <td class="px-2">:</td>
                <td><span class="status-badge">REKAPITULASI RESMI</span></td>
            </tr>
        </table>
    </div>

    <!-- Main Table -->
    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Ticket ID</th>
                <th width="10%">Date</th>
                <th width="15%">Associate Name</th>
                <th width="12%">Service Type</th>
                <th>Issue Details / Complaint</th>
                <th width="12%">Technician</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) : 
            ?>
            <tr>
                <td class="text-center text-muted small"><?php echo $no++; ?></td>
                <td class="text-center tech-font"><?php echo $row['kd_lapor']; ?></td>
                <td class="text-center"><?php echo date('d/m/Y', strtotime($row['tgl_lapor'])); ?></td>
                <td class="fw-bold text-uppercase"><?php echo $row['nm_pelanggan']; ?></td>
                <td class="text-center"><?php echo $row['nm_layanan']; ?></td>
                <td><?php echo $row['keluhan']; ?></td>
                <td class="text-center fw-bold text-primary">
                    <?php echo $row['nm_teknisi'] ? strtoupper($row['nm_teknisi']) : '-'; ?>
                </td>
                <td class="text-center">
                    <span class="status-badge"><?php echo $row['status_lapor']; ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Footer Area -->
    <div class="row signature-area">
        <div class="col-8">
            <p class="small text-muted mb-0"><i>* This is an official computer-generated document from Batam Marriott IT Support System.</i></p>
            <p class="small text-muted"><i>* Printed by: <?php echo $_SESSION['nama_lengkap'] ?? 'System Admin'; ?></i></p>
        </div>
        <div class="col-4 text-end">
            <p>Batam, <?php echo date('d F Y'); ?></p>
            <p style="margin-bottom: 70px;"><strong>Verified by,</strong></p>
            <p class="mb-0">__________________________</p>
            <p class="fw-bold" style="color: #A80023;">Eagle Whidy Armanto</p>
        </div>
    </div>

    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>