<?php
include "../config.php";
$filter_level = isset($_GET['filter_level']) ? $_GET['filter_level'] : '';

if ($filter_level != '') {
    $query = mysqli_query($conn, "SELECT * FROM users WHERE level='$filter_level' ORDER BY level ASC");
} else {
    $query = mysqli_query($conn, "SELECT * FROM users ORDER BY level ASC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report_Associate_Data_<?php echo date('Ymd'); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
	<link rel="icon" type="image/png" href="../favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&family=JetBrains+Mono&display=swap" rel="stylesheet">
    
    <style>
        body { 
            padding: 50px; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #333;
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

        .logo-marriott { height: 50px; }
        .logo-it { height: 45px; filter: grayscale(1); }
        .divider-v { width: 2px; height: 40px; background: #ddd; }

        .report-title { text-align: right; }
        .report-title h2 { 
            margin: 0; 
            font-weight: 800; 
            color: #A80023;
            font-size: 1.5rem;
            letter-spacing: 1px;
        }
        .report-title p { margin: 0; font-size: 0.85rem; color: #666; }

        /* Table Styling */
        .table { font-size: 0.9rem; border-color: #eee; }
        .table thead {
            background-color: #f8f9fa !important;
            border-top: 2px solid #333;
        }
        .table thead th { 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1px; 
            padding: 12px;
        }

        .tech-font { font-family: 'JetBrains Mono', monospace; font-weight: bold; color: #A80023; }
        
        .badge-level {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            background: #f0f0f0;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Footer Tanda Tangan */
        .signature-area {
            margin-top: 50px;
            font-size: 0.9rem;
        }

        @media print {
            .btn-print { display: none; }
            body { padding: 20px; }
            .table thead { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
            .header-report { border-bottom: 3px solid #A80023 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header-report">
        <div class="logo-box">
            <img src="../marriott-logo.png" class="logo-marriott" alt="Marriott Logo">
            <div class="divider-v"></div>
            <img src="../logofix.png" class="logo-it" alt="IT Dept Logo">
        </div>
        <div class="report-title">
            <h2>SYSTEM ASSOCIATE REPORT</h2>
            <p>Batam Marriott Hotel Harbour Bay</p>
            <p class="small text-muted">Internal Infrastructure & Operations</p>
        </div>
    </div>

    <div class="mb-4">
        <table class="table table-sm table-borderless w-auto" style="font-size: 0.8rem;">
            <tr>
                <td class="text-muted">Generated Date</td>
                <td class="px-3">:</td>
                <td><strong><?php echo date('d F Y / H:i'); ?></strong></td>
            </tr>
            <tr>
                <td class="text-muted">Filter Category</td>
                <td class="px-3">:</td>
                <td>
                    <span class="badge-level">
                        <?php 
                            $category = ($filter_level != '') ? $filter_level : 'ALL LEVELS';
                            // Mengganti teks filter jika mengandung kata pelanggan
                            echo str_ireplace('pelanggan', 'ASSOCIATE', $category); 
                        ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <table class="table table-bordered align-middle">
        <thead class="text-center">
            <tr>
                <th width="5%">No</th>
                <th width="40%" class="text-start">Full Name</th>
                <th width="30%" class="text-start">Network ID (Username)</th>
                <th width="25%">Access Level</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = mysqli_fetch_assoc($query)) : 
                // Logika penggantian teks Pelanggan ke Associate
                $display_level = str_ireplace('pelanggan', 'ASSOCIATE', $row['level']);
            ?>
            <tr>
                <td class="text-center text-muted small"><?php echo $no++; ?></td>
                <td class="fw-bold"><?php echo strtoupper($row['nama_lengkap']); ?></td>
                <td class="tech-font"><?php echo $row['username']; ?></td>
                <td class="text-center">
                    <span class="badge-level"><?php echo $display_level; ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="row signature-area">
        <div class="col-8">
            <p class="small text-muted"><i>* This document is an automated system generated report.</i></p>
        </div>
        <div class="col-4 text-end">
            <p>Batam, <?php echo date('d F Y'); ?></p>
            <p style="margin-bottom: 60px;"><strong>Authorized by,</strong></p>
            <p class="mb-0">__________________________</p>
            <p class="fw-bold" style="color: #A80023;">Eagle Whidy Armanto</p>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>