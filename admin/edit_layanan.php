<?php
session_start();
include "../config.php";

// Proteksi halaman: Hanya level Admin yang boleh masuk
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

// 1. Ambil data lama berdasarkan ID
if (!isset($_GET['id'])) {
    header("location:data_layanan.php");
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM m_layanan WHERE kd_layanan='$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='data_layanan.php';</script>";
    exit();
}

// 2. Proses Simpan Perubahan
if (isset($_POST['update'])) {
    $nm = mysqli_real_escape_string($conn, $_POST['nm_layanan']);
    $ket = mysqli_real_escape_string($conn, $_POST['ket_layanan']);

    $update = mysqli_query($conn, "UPDATE m_layanan SET nm_layanan='$nm', ket_layanan='$ket' WHERE kd_layanan='$id'");

    if ($update) {
        echo "<script>alert('Layanan berhasil diperbarui!'); window.location.href='data_layanan.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan | Marriott IT Support</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap & Icons -->
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .edit-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card-custom {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .card-header-tech {
            background: white;
            padding: 30px 30px 10px 30px;
            border: none;
            text-align: center;
        }

        .card-header-tech h4 {
            font-weight: 800;
            color: var(--dark-tech);
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }

        .card-header-tech p {
            font-size: 0.85rem;
            color: #888;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background-color: #f8f9fa;
            border: 2px solid #f8f9fa;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: var(--marriott-red);
            box-shadow: none;
            outline: none;
        }

        .btn-marriott {
            background: var(--marriott-red);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-marriott:hover {
            background: #80001B;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(168, 0, 35, 0.3);
            color: white;
        }

        .btn-cancel {
            background: #f1f1f1;
            color: #666;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background: #e5e5e5;
            color: #333;
        }

        .service-id-tag {
            display: inline-block;
            background: #fff5f5;
            color: var(--marriott-red);
            font-size: 0.7rem;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 50px;
            margin-bottom: 15px;
        }

        /* Eagle Footer Styling */
        footer {
            width: 100%;
            padding: 20px 0;
            background: transparent;
        }
    </style>
</head>
<body>

    <div class="edit-container">
        <div class="card-custom">
            <div class="card-header-tech">
                <div class="service-id-tag">SERVICE ID: <?php echo $id; ?></div>
                <h4>Update Service</h4>
                <p>Modify IT service details in the system</p>
            </div>
            
            <div class="card-body p-4">
                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Service Name</label>
                        <input type="text" name="nm_layanan" class="form-control" 
                               value="<?php echo htmlspecialchars($data['nm_layanan']); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Description / Remarks</label>
                        <textarea name="ket_layanan" class="form-control" rows="4" 
                                  placeholder="Provide details about this service..."><?php echo htmlspecialchars($data['ket_layanan']); ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="update" class="btn btn-marriott">
                            <i class="bi bi-check-circle me-2"></i> Save Changes
                        </button>
                        <a href="data_layanan.php" class="btn btn-cancel">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Identitas Eagle Footer -->
    <footer>
        <div class="container text-center">
            <p class="text-muted small mb-1">Marriott International - IT Department Portal</p>
            <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.8rem; letter-spacing: 1px;">
                DEVELOPED BY EAGLE - IT INTERN &copy; 2026
            </p>
        </div>
    </footer>

</body>
</html>