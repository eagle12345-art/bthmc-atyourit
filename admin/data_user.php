<?php
session_start();
include "../config.php"; 

if (!isset($_SESSION['level']) || $_SESSION['level'] != "Admin") {
    header("location:../index.php");
    exit();
}

// Pemetaan role untuk filter
$role_map = [
    "Admin"      => "Admin",
    "Associate"  => "Pelanggan",
    "Technician" => "Teknisi"
];

// Query filter
$filter_level = isset($_GET['filter_level']) ? $_GET['filter_level'] : '';
$query_str = "SELECT * FROM users";
if ($filter_level != '') { $query_str .= " WHERE level='$filter_level'"; }
$query_str .= " ORDER BY level ASC";
$query = mysqli_query($conn, $query_str);

// Ambil password admin yang login untuk verifikasi modal
$session_user = $_SESSION['username'];
$check_admin  = mysqli_query($conn, "SELECT password FROM users WHERE username='$session_user'");
$admin_data   = mysqli_fetch_assoc($check_admin);
$current_admin_pass = ($admin_data) ? $admin_data['password'] : '';

// Ambil data telepon dari m_pelanggan / m_teknisi untuk ditampilkan
function getTelepon($conn, $username, $level) {
    if ($level == 'Pelanggan') {
        $q = mysqli_query($conn, "SELECT tlp_pelanggan FROM m_pelanggan WHERE username='$username'");
        $d = mysqli_fetch_assoc($q);
        return ($d && $d['tlp_pelanggan'] != '-') ? $d['tlp_pelanggan'] : '-';
    } elseif ($level == 'Teknisi') {
        $q = mysqli_query($conn, "SELECT tlp_teknisi FROM m_teknisi WHERE username='$username'");
        $d = mysqli_fetch_assoc($q);
        return ($d && $d['tlp_teknisi'] != '-') ? $d['tlp_teknisi'] : '-';
    }
    return '-';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | IT Portal</title>
    <link rel="icon" type="image/png" href="../favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        :root { --marriott-red: #A80023; --dark-gray: #212529; }
        body { background-color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .top-bar { background: white; padding: 15px 40px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 1000; }
        .btn-back { border: 1px solid #dee2e6; background: transparent; color: #333; border-radius: 50px; padding: 6px 22px; font-weight: 500; text-decoration: none; font-size: 14px; }
        .btn-add { background: var(--marriott-red); color: white; border: none; border-radius: 6px; padding: 10px 20px; font-weight: 600; font-size: 14px; transition: 0.3s; }
        .btn-add:hover { background: #80001a; transform: translateY(-1px); }
        .filter-card { background: white; border-radius: 15px; padding: 25px 35px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .filter-label { font-weight: 800; color: #333; font-size: 13px; letter-spacing: 0.5px; margin-right: 20px; white-space: nowrap; display: inline-block; }
        .form-select-custom { border-radius: 50px; border: 2px solid #cfe2ff; padding: 10px 25px; font-size: 14px; width: 300px; color: #333; font-weight: 500; }
        .btn-apply { background: var(--dark-gray); color: white; border-radius: 50px; padding: 10px 35px; border: none; font-weight: 700; font-size: 14px; margin-left: 15px; transition: 0.2s; }
        .btn-apply:hover { background: #000; }
        .main-card { background: white; border-radius: 15px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.03); overflow: hidden; }
        .table thead th { background-color: #fdfdfd; padding: 18px 25px; color: #444; font-weight: 700; font-size: 13px; text-transform: uppercase; border-bottom: 1px solid #f0f0f0; }
        .table tbody td { padding: 18px 25px; font-size: 14px; border-bottom: 1px solid #f8f9fa; }
        .badge-level { padding: 6px 14px; border-radius: 6px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; }
        .bg-admin { background: #ffebeb; color: var(--marriott-red); }
        .bg-associate { background: #eef2ff; color: #4338ca; }
        .bg-technician { background: #f0fdf4; color: #15803d; }
        code { font-family: 'JetBrains Mono'; color: #d63384; font-size: 13px; background: #fdf2f8; padding: 2px 6px; border-radius: 4px; }
        .telepon-badge { font-size: 12px; color: #666; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; }
        footer { margin-top: auto; padding: 30px 0; border-top: 1px solid #eee; background: white; }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="d-flex align-items-center">
            <i class="bi bi-people-fill fs-4 me-3 text-danger"></i>
            <h5 class="m-0 fw-bold">User Management</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="../dashboard.php" class="btn-back"><i class="bi bi-arrow-left me-2"></i> Back</a>
            <a href="cetak_user.php?filter_level=<?= $filter_level; ?>" class="btn btn-light border btn-sm rounded-pill px-4 py-2"><i class="bi bi-printer me-1"></i> Print</a>
            <button class="btn-add ms-2" onclick="requestVerification('ADD')"><i class="bi bi-plus-lg me-2"></i> ADD NEW USER</button>
        </div>
    </div>

    <div class="container-fluid px-5 py-4">
        <!-- Filter Card -->
        <div class="filter-card">
            <form method="GET" class="d-flex align-items-center">
                <span class="filter-label">FILTER ROLE:</span>
                <select name="filter_level" class="form-select form-select-custom">
                    <option value="">Show All Roles</option>
                    <?php foreach($role_map as $eng => $db): ?>
                        <option value="<?= $db ?>" <?= $filter_level == $db ? 'selected' : '' ?>><?= $eng ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-apply shadow-sm">APPLY FILTER</button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="main-card">
            <table class="table align-middle m-0">
                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th>Staff Full Name</th>
                        <th>Username</th>
                        <th>Telepon</th>
                        <th class="text-center">Role</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query) > 0) {
                        while ($row = mysqli_fetch_assoc($query)) : 
                            $display_role = "ASSOCIATE"; $badge_class = "bg-associate";
                            if(strtolower($row['level']) == 'admin') { $display_role = "ADMIN"; $badge_class = "bg-admin"; }
                            elseif(strtolower($row['level']) == 'teknisi') { $display_role = "TECHNICIAN"; $badge_class = "bg-technician"; }
                            $tlp = getTelepon($conn, $row['username'], $row['level']);
                    ?>
                    <tr>
                        <td class="text-muted"><?= $no++; ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <code><?= htmlspecialchars($row['username']); ?></code>
                                <i class="bi bi-shield-lock text-muted" style="cursor:pointer" 
                                   onclick="requestVerification('VIEW_PASS', '', '', '<?= $row['username'] ?>', '', '<?= $row['password'] ?>')" 
                                   title="View Password"></i>
                            </div>
                        </td>
                        <td>
                            <?php if ($row['level'] != 'Admin'): ?>
                                <span class="telepon-badge"><i class="bi bi-telephone me-1"></i><?= $tlp ?></span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="badge-level <?= $badge_class ?>"><?= $display_role ?></span></td>
                        <td class="text-end">
                            <button class="btn btn-sm text-primary border-0 me-2" 
                                    onclick="requestVerification('EDIT', '<?= $row['id_users'] ?>', '<?= addslashes($row['nama_lengkap']) ?>', '<?= addslashes($row['username']) ?>', '<?= $row['level'] ?>', '', '<?= $tlp ?>')">
                                <i class="bi bi-pencil-square fs-5"></i>
                            </button>
                            <button class="btn btn-sm text-danger border-0" 
                                    onclick="requestVerification('DELETE', '<?= $row['id_users'] ?>', '<?= addslashes($row['nama_lengkap']) ?>')">
                                <i class="bi bi-trash3 fs-5"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; } else { ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No users found.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p class="text-muted small mb-1">Marriott International - IT Department Internal Portal</p>
            <p class="fw-bold mb-0" style="color: var(--marriott-red); font-size: 0.75rem; letter-spacing: 1.5px; text-transform: uppercase;">
                Powered by Eagle System &copy; 2026
            </p>
        </div>
    </footer>

    <!-- MODAL 1: VERIFIKASI ADMIN -->
    <div class="modal fade" id="modalVerify" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3"><i class="bi bi-shield-fill-check text-danger" style="font-size: 3rem;"></i></div>
                    <h6 class="fw-bold">Security Check</h6>
                    <p class="text-muted small">Enter admin password to continue</p>
                    <input type="password" id="authPassword" class="form-control mb-3 text-center rounded-pill border-2" placeholder="••••••••">
                    <div class="d-grid gap-2">
                        <button onclick="checkAuth()" class="btn btn-danger rounded-pill fw-bold">Verify Access</button>
                        <button class="btn btn-link btn-sm text-muted text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: TAMPIL PASSWORD -->
    <div class="modal fade" id="modalReveal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="background: #1a1a1a; border-radius: 15px;">
                <div class="modal-body p-4 text-center text-white">
                    <small class="text-uppercase text-muted fw-bold" style="letter-spacing: 1px;">Password for <span id="revealTarget" class="text-danger"></span></small>
                    <h2 class="fw-bold my-3" id="revealValue" style="letter-spacing: 2px;"></h2>
                    <button class="btn btn-outline-light btn-sm rounded-pill w-100" data-bs-dismiss="modal">Got it</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: FORM USER (ADD/EDIT) -->
    <div class="modal fade" id="modalForm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form method="POST" action="proses_user.php">
                    <div class="modal-header bg-dark text-white border-0">
                        <h6 class="modal-title fw-bold" id="modalTitle">User Details</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <input type="hidden" name="id_users" id="form_id">
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Full Name</label>
                            <input type="text" name="full_name" id="form_name" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Username</label>
                            <input type="text" name="username" id="form_user" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold mb-1" id="passLabel">Password</label>
                            <input type="password" name="password" id="form_pass" class="form-control rounded-3" placeholder="Leave blank if no change">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Access Level</label>
                            <select name="level" id="form_level" class="form-select rounded-3" required onchange="toggleTelepon()">
                                <option value="Admin">Admin (Full Access)</option>
                                <option value="Pelanggan">Associate (Standard)</option>
                                <option value="Teknisi">Technician (Service)</option>
                            </select>
                        </div>
                        <!-- Field telepon: hanya muncul jika level Pelanggan atau Teknisi -->
                        <div class="mb-3" id="field_telepon" style="display:none;">
                            <label class="small fw-bold mb-1">No. Telepon</label>
                            <input type="text" name="telepon" id="form_telepon" class="form-control rounded-3" placeholder="e.g. 08123456789">
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="submit" id="btnSubmit" name="save_user" class="btn btn-danger w-100 py-2 fw-bold rounded-pill shadow">SAVE CHANGES</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden form untuk delete -->
    <form id="formDelete" method="POST" action="proses_user.php" style="display:none;">
        <input type="hidden" name="id_users" id="delete_id">
        <input type="hidden" name="confirm_delete" value="1">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentType = '';
        let data = {};
        const adminPass = "<?= $current_admin_pass ?>";

        function toggleTelepon() {
            const level = document.getElementById('form_level').value;
            const show = (level === 'Pelanggan' || level === 'Teknisi');
            document.getElementById('field_telepon').style.display = show ? 'block' : 'none';
        }

        function requestVerification(type, id='', nama='', user='', level='', pass='', tlp='') {
            currentType = type;
            data = { id, nama, user, level, pass, tlp };
            document.getElementById('authPassword').value = '';
            new bootstrap.Modal(document.getElementById('modalVerify')).show();
        }

        function checkAuth() {
            const inputPass = document.getElementById('authPassword').value;
            if (inputPass === adminPass) {
                bootstrap.Modal.getInstance(document.getElementById('modalVerify')).hide();
                setTimeout(() => {
                    if (currentType === 'VIEW_PASS') {
                        document.getElementById('revealTarget').innerText = data.user;
                        document.getElementById('revealValue').innerText = data.pass;
                        new bootstrap.Modal(document.getElementById('modalReveal')).show();
                    } else if (currentType === 'DELETE') {
                        if (confirm('Are you sure you want to delete user: ' + data.nama + '?')) {
                            document.getElementById('delete_id').value = data.id;
                            document.getElementById('formDelete').submit();
                        }
                    } else {
                        const isEdit = currentType === 'EDIT';
                        document.getElementById('form_id').value     = isEdit ? data.id    : '';
                        document.getElementById('form_name').value   = isEdit ? data.nama  : '';
                        document.getElementById('form_user').value   = isEdit ? data.user  : '';
                        document.getElementById('form_level').value  = isEdit ? data.level : 'Pelanggan';
                        document.getElementById('form_telepon').value = isEdit ? (data.tlp === '-' ? '' : data.tlp) : '';
                        document.getElementById('modalTitle').innerText  = isEdit ? 'Update User Account' : 'Register New User';
                        document.getElementById('btnSubmit').name        = isEdit ? 'update_user' : 'save_user';
                        document.getElementById('form_pass').required    = !isEdit;
                        document.getElementById('passLabel').innerText   = isEdit ? 'New Password (Optional)' : 'Password (Required)';
                        toggleTelepon();
                        new bootstrap.Modal(document.getElementById('modalForm')).show();
                    }
                }, 400);
            } else {
                alert('Access Denied: Incorrect Admin Password.');
            }
        }
    </script>
</body>
</html>