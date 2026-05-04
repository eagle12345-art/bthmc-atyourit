<?php
session_start();
include "../config.php"; 

// Proteksi halaman: Hanya level Admin yang boleh masuk
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

// Proses Hapus Layanan langsung di halaman ini
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($conn, "DELETE FROM m_layanan WHERE kd_layanan='$id'");
    if($delete) {
        header("location:data_layanan.php");
        exit();
    }
}

// Ambil semua data dari tabel m_layanan
$query = mysqli_query($conn, "SELECT * FROM m_layanan ORDER BY kd_layanan ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Layanan | Marriott IT Support</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    
    <!-- FIX: Favicon Nama File favicon.png -->
    <link rel="icon" type="image/png" href="../favicon.png">
    
    <!-- Bootstrap & Icons -->
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
            position: sticky;
            top: 0;
            z-index: 1000;
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

        .card-header-tech {
            background: white;
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .btn-action {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: 0.2s;
            text-decoration: none;
        }

        .btn-edit { background: #fff8e1; color: #ff8f00; }
        .btn-delete { background: #fff5f5; color: var(--marriott-red); }
        .btn-action:hover { transform: translateY(-2px); opacity: 0.8; }

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

        .id-badge {
            font-family: 'JetBrains Mono', monospace;
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            color: var(--dark-tech);
            border: 1px solid #eee;
        }

        footer {
            background: white;
            border-top: 1px solid #eee;
            padding: 20px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <div class="top-bar shadow-sm">
        <div class="page-title">
            <h4><i class="bi bi-cpu me-2"></i>Master Services IT</h4>
        </div>
        <div>
            <a href="../dashboard.php" class="btn btn-outline-dark btn-sm rounded-pill px-3 me-2">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
            <button class="btn btn-marriott" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-lg me-2"></i> Add Services
            </button>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="card-custom">
            <div class="card-header-tech">
                <span class="text-muted small fw-bold">DATABASE: M_LAYANAN</span>
                <span class="badge rounded-pill bg-light text-dark border">System Active</span>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" width="10%">Code</th>
                            <th width="30%">Service Name</th>
                            <th>Description</th>
                            <th class="text-end" width="15%">Options</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($query)) : ?>
                        <tr>
                            <td class="text-center">
                                <span class="id-badge"><?php echo str_pad($row['kd_layanan'], 2, "0", STR_PAD_LEFT); ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo $row['nm_layanan']; ?></div>
                            </td>
                            <td>
                                <div class="text-muted small">
                                    <?php echo $row['ket_layanan'] ? $row['ket_layanan'] : '<i class="text-light">No description provided...</i>'; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="edit_layanan.php?id=<?php echo $row['kd_layanan']; ?>" class="btn-action btn-edit me-1" title="Edit Layanan">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="?hapus=<?php echo $row['kd_layanan']; ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Yakin ingin menghapus layanan <?php echo $row['nm_layanan']; ?>?')" 
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

    <!-- Footer Identitas Eagle -->
    <footer>
        <div class="container text-center">
            <p class="text-muted small mb-1">Marriott International - IT Department Portal</p>
            <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.85rem; letter-spacing: 1px;">
                DEVELOPED BY EAGLE - IT INTERN &copy; 2026
            </p>
        </div>
    </footer>

    <!-- Modal Tambah Layanan -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none;">
                <form action="proses_layanan.php" method="POST">
                    <div class="modal-header border-0 pt-4 px-4">
                        <h5 class="fw-800">New Service Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">SERVICE NAME</label>
                            <input type="text" name="nm_layanan" class="form-control form-control-lg" style="font-size: 0.9rem; border-radius: 10px;" required placeholder="e.g. WiFi / IPTV">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">DESCRIPTION (OPTIONAL)</label>
                            <textarea name="ket_layanan" class="form-control" style="font-size: 0.9rem; border-radius: 10px;" rows="3" placeholder="Enter service details..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4">
                        <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="simpan" class="btn btn-marriott px-4">Save Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>