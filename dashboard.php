<?php
session_start();
include "config.php"; 

// Cek apakah user sudah login
if (!isset($_SESSION['level'])) {
    header("location:index.php");
    exit();
}

$level = $_SESSION['level'];
$nama  = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Portal - Batam Marriott Hotel</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="favicon.png">
    <!-- Bootstrap & Fonts -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Playfair+Display:ital,wght@0,600;1,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --m-red: #A80023;
            --m-gold: #D4AF37;
            --m-black: #1C1C1C;
            --cyber-blue: rgba(0, 123, 255, 0.08);
        }

        body { 
            background-color: #f8f9fa; 
            background-image: 
                linear-gradient(rgba(255,255,255,0.92), rgba(255,255,255,0.92)),
                url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--m-black);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Cyber Background Grid */
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(90deg, var(--cyber-blue) 1px, transparent 1px),
                        linear-gradient(var(--cyber-blue) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
        }

        /* --- NAVBAR --- */
        .navbar-marriott { 
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
            padding: 15px 0;
            z-index: 1000;
        }

        .logo-main { height: 45px; width: auto; }

        .nav-center-tagline {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Playfair Display', serif;
            font-size: 2rem; /* Sedikit disesuaikan agar tidak terlalu dominan di layar kecil */
            color: var(--m-red);
            font-weight: 700;
            font-style: italic;
            letter-spacing: -1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.05);
            white-space: nowrap;
        }

        /* --- HEADER BOX --- */
        .header-box {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 30px;
            padding: 60px 50px;
            margin-top: 40px;
            margin-bottom: 50px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
        }

        /* Scanning Animation */
        .header-box::after {
            content: "";
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--m-red), transparent);
            animation: scan 4s linear infinite;
        }

        @keyframes scan {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .logo-it-header {
            position: absolute;
            right: 50px; 
            top: 50%;
            transform: translateY(-50%);
            height: 180px;
            width: auto;
            opacity: 0.18;
            filter: grayscale(1);
            pointer-events: none;
        }

        .it-badge {
            background: var(--m-red);
            color: white;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 4px;
            padding: 8px 22px;
            border-radius: 50px;
            margin-bottom: 25px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(168, 0, 35, 0.2);
        }

        .welcome-text {
            font-weight: 800; 
            font-size: clamp(2rem, 5vw, 3.5rem); /* Responsive font size */
            letter-spacing: -2px;
            margin-bottom: 10px;
        }

        /* --- CARDS --- */
        .card-it {
            background: white;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 24px;
            padding: 45px 35px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            position: relative;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .card-it:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(168, 0, 35, 0.12);
            border-color: var(--m-gold);
        }

        .icon-wrapper {
            width: 80px; height: 80px;
            background: #fdf2f3;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 25px;
            transition: 0.3s;
        }

        .card-it:hover .icon-wrapper {
            background: var(--m-red);
            color: white;
            box-shadow: 0 0 20px rgba(168, 0, 35, 0.3);
        }

        /* --- FOOTER --- */
        footer {
            margin-top: 80px;
            padding-bottom: 40px;
            color: #777;
            font-weight: 600;
        }

        .intern-name { color: var(--m-red); font-weight: 800; }

        .btn-logout {
            border: 2px solid var(--m-red);
            color: var(--m-red);
            border-radius: 50px;
            font-weight: 700;
            padding: 8px 25px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-logout:hover { background: var(--m-red); color: white; }

        /* Media Query for smaller screens */
        @media (max-width: 768px) {
            .nav-center-tagline { display: none; }
            .logo-it-header { height: 100px; right: 20px; }
            .header-box { padding: 40px 30px; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-marriott sticky-top">
        <div class="container d-flex justify-content-between align-items-center position-relative">
            <a class="navbar-brand" href="#">
                <img src="marriott-logo.png" alt="Marriott" class="logo-main">
            </a>

            <div class="nav-center-tagline">
                ~ At Your IT ~
            </div>

            <a href="logout.php" class="btn btn-logout btn-sm">LOGOUT</a>
        </div>
    </nav>

    <div class="container">
        <!-- Header Section -->
        <div class="header-box">
            <img src="logofix.png" alt="IT Logo" class="logo-it-header">
            
            <div class="d-flex flex-column align-items-start position-relative" style="z-index: 2;">
                <span class="it-badge">SECURITY DASHBOARD</span>
                <h1 class="welcome-text">Hello, <?php echo htmlspecialchars(explode(' ', trim($nama))[0]); ?>.</h1>
                <p class="text-muted fs-5 mb-4">Secure Gateway for Batam Marriott Hotel Digital Operations.</p>
                <div class="d-flex gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">System Online</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">Encrypted Session</span>
                </div>
            </div>
        </div>

       <!-- Menu Grid -->
        <div class="row g-4 justify-content-center">
            
            <?php 
            // Normalisasi: Ubah ke huruf kecil semua agar pengecekan akurat
            $level_check = strtolower(trim($level)); 
            ?>

            <?php if ($level_check == "admin") : ?>
                <!-- Menu Admin -->
                <div class="col-md-4">
                    <a href="admin/data_keluhan.php" class="card-it text-center">
                        <div class="icon-wrapper">🛡️</div>
                        <h5 class="fw-bold">INCIDENT CONTROL</h5>
                        <p class="text-muted small">Monitor and resolve system vulnerabilities.</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="admin/data_user.php" class="card-it text-center">
                        <div class="icon-wrapper">🔑</div>
                        <h5 class="fw-bold">ACCESS CONTROL</h5>
                        <p class="text-muted small">Manage digital keys and user authorities.</p>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="admin/data_layanan.php" class="card-it text-center">
                        <div class="icon-wrapper">💾</div>
                        <h5 class="fw-bold">CORE SERVICES</h5>
                        <p class="text-muted small">Server-side service configurations.</p>
                    </a>
                </div>

            <?php elseif ($level_check == "pelanggan" || $level_check == "associate") : ?>
                <!-- Menu Associate / Pelanggan -->
                <div class="col-md-5">
                    <a href="associate/lapor_gangguan.php" class="card-it text-center">
                        <div class="icon-wrapper">⚡</div>
                        <h5 class="fw-bold">FIRE TICKET</h5>
                        <p class="text-muted">Report technical failures for immediate response.</p>
                        <small class="fw-bold text-danger">INITIALIZE REQUEST →</small>
                    </a>
                </div>
                <div class="col-md-5">
                    <a href="associate/history_laporan.php" class="card-it text-center">
                        <div class="icon-wrapper">📡</div>
                        <h5 class="fw-bold">LIVE TRACKING</h5>
                        <p class="text-muted">Active surveillance of your support status.</p>
                        <small class="fw-bold text-warning">VIEW LOGS →</small>
                    </a>
                </div>

            <?php elseif ($level_check == "teknisi") : ?>
                <!-- Menu Teknisi -->
                <div class="col-md-6">
                    <a href="teknisi_it/tugas_teknisi.php" class="card-it text-center">
                        <div class="icon-wrapper">🛠️</div>
                        <h5 class="fw-bold">MAINTENANCE OPS</h5>
                        <p class="text-muted">Hardware integrity and repair missions.</p>
                    </a>
                </div>
            <?php endif; ?>
            
        </div>

    <footer class="text-center">
        <p>by <span class="intern-name">Eagle</span> — IT Intern Batam Marriott Hotel Harbour Bay © 2026</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>