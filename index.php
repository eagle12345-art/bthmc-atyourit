<?php
session_start();
include "config.php"; 

// 1. Cek jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['level'])) {
    header("location:dashboard.php");
    exit();
}

$error = "";

// 2. Proses Login
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query ke tabel 'users'
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);

        $_SESSION['username']     = $data['username'];
        $_SESSION['level']        = $data['level'];        
        $_SESSION['nama_lengkap'] = $data['nama_lengkap']; 

        header("location:dashboard.php");
        exit();
    } else {
        $error = "Access Denied: Invalid Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | At Your IT! - Marriott IT Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <style>
        :root {
            --marriott-red: #A80023;
            --tech-dark: #0f1111;
        }

        body, html {
            height: 100%; margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow: hidden;
            background-color: white;
        }

        .main-container { display: flex; height: 100vh; width: 100vw; }

        /* SISI KIRI: TECH VISUAL */
        .visual-side {
            flex: 1.2;
            background-color: var(--tech-dark);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px;
            color: white;
        }

        .visual-side::before {
            content: ""; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background-image: 
                linear-gradient(rgba(168, 0, 35, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(168, 0, 35, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            mask-image: radial-gradient(ellipse at center, black, transparent 80%);
        }

        .tech-content { position: relative; z-index: 2; }

        .it-badge {
            background: var(--marriott-red);
            padding: 5px 15px; border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem; margin-bottom: 25px;
            display: inline-block; letter-spacing: 1px;
        }

        .headline { font-size: 3.8rem; font-weight: 800; line-height: 1; margin-bottom: 15px; }
        .headline span { color: var(--marriott-red); }

        .terminal-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem; color: #4ade80;
            opacity: 0.7; margin-top: 40px;
            border-left: 3px solid var(--marriott-red);
            padding-left: 20px;
        }

        /* SISI KANAN: LOGIN FORM */
        .form-side {
            flex: 0.8;
            display: flex; 
            flex-direction: column; /* Ditambah agar footer bisa di bawah */
            align-items: center;
            justify-content: center; padding: 40px;
            background: #ffffff;
            position: relative;
        }

        .login-box { width: 100%; max-width: 420px; flex-grow: 1; display: flex; flex-direction: column; justify-content: center; }

        .logo-group {
            display: flex; align-items: center; gap: 25px; margin-bottom: 50px;
        }

        .logo-marriott { height: 50px; width: auto; }
        .logo-it { height: 45px; width: auto; filter: grayscale(1); opacity: 0.9; }
        .divider { width: 2px; height: 35px; background: #eee; }

        .form-label-tech {
            font-size: 0.75rem; font-weight: 800; color: #999;
            letter-spacing: 1px; margin-bottom: 8px; display: block;
        }

        .form-control {
            border-radius: 12px; padding: 15px;
            border: 1.5px solid #eee; background: #fcfcfc;
            font-size: 1rem; transition: 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(168, 0, 35, 0.08);
            border-color: var(--marriott-red);
            background: white;
        }

        .btn-login {
            background: var(--marriott-red); color: white;
            width: 100%; padding: 16px; border-radius: 12px;
            border: none; font-weight: 700; margin-top: 15px;
            text-transform: uppercase; letter-spacing: 1.5px;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #80001B; transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(168, 0, 35, 0.2);
        }

        .error-msg {
            background: rgba(168, 0, 35, 0.05);
            color: var(--marriott-red);
            border-radius: 10px; border: 1px solid rgba(168, 0, 35, 0.2);
            font-size: 0.9rem; padding: 14px;
            text-align: center; margin-bottom: 25px;
            font-weight: 600;
        }

        /* Eagle Footer Styling */
        footer {
            padding: 20px 0;
            width: 100%;
        }

        @media (max-width: 992px) { .visual-side { display: none; } }
    </style>
</head>
<body>

<div class="main-container">
    <!-- Section Kiri (IT Branding) -->
    <div class="visual-side">
        <div class="tech-content">
            <div class="it-badge">>_ SYSTEM_STATUS: READY</div>
            <h1 class="headline">At Your <span>IT!</span></h1>
            <p class="lead text-secondary">Batam Marriott Hotel Harbour Bay <br>Infrastructure & Support Portal.</p>
            
            <div class="terminal-text">
                <div>$ initializing_secure_handshake... [OK]</div>
                <div>$ node_active: Marriott_Batam_Srv</div>
                <div>$ protocol: v3.0.final</div>
                <div>$ awaiting_authentication_</div>
            </div>
        </div>
        
        <div style="position: absolute; bottom: 40px; left: 80px; font-family: 'JetBrains Mono'; opacity: 0.2; font-size: 11px; letter-spacing: 2px;">
            01001101 01000001 01010010 01010010 01001001 01001111 01010100 01010100
        </div>
    </div>

    <!-- Section Kanan (Login Form) -->
    <div class="form-side">
        <div class="login-box">
            <div class="logo-group">
                <img src="marriott-logo.png" alt="Marriott" class="logo-marriott">
                <div class="divider"></div>
                <img src="logofix.png" alt="IT" class="logo-it">
            </div>

            <h3 class="fw-800 mb-1">Access Terminal</h3>
            <p class="text-muted small mb-4">Identify yourself to establish session.</p>

            <?php if ($error != ""): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3 text-start">
                    <label class="form-label-tech">OPERATOR ID</label>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                </div>
                
                <div class="mb-4 text-start">
                    <label class="form-label-tech">ENCRYPTION KEY</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" name="login" class="btn btn-login">Initialize Session</button>
            </form>
        </div>

        <!-- Identitas Eagle Footer (Sama dengan Dashboard & Assign) -->
        <footer class="text-center">
            <div class="container">
                <p class="text-muted small mb-1" style="font-size: 0.7rem;">Batam Marriott Hotel Harbour Bay — IT Department</p>
                <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.8rem; letter-spacing: 1.2px; text-transform: uppercase;">
                    Developed by Eagle — IT Intern © 2026
                </p>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>