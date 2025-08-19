<?php
include 'process.php';

// Default to root path
$root = '/';
$normalizedRoot = ($root === '/') ? '/' : '/' . trim($root, '/') . '/';

// Load constants if already installed
if (file_exists('../app/constants.php')) {
    include '../app/constants.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hackzz Installer</title>
  <link href="../public/css/bootstrap.min.css" rel="stylesheet">
  <link href="../public/css/all.min.css" rel="stylesheet">
  <link href="../public/css/custom.css" rel="stylesheet">
  <style>
    body {
      background-color: #0f172a;
      color: #e2e8f0;
      font-family: 'Segoe UI', sans-serif;
    }
    .card {
      background-color: #1e293b;
      border: 1px solid #334155;
      border-radius: 0.75rem;
      box-shadow: 0 0 10px rgba(0, 255, 255, 0.05);
    }
    .card-header {
      background-color: #1e40af;
      color: #f8fafc;
      font-weight: 600;
      border-bottom: 1px solid #334155;
    }
    .form-control {
      background-color: #0f172a;
      color: #f8fafc;
      border: 1px solid #475569;
      border-radius: 0.5rem;
    }
    .form-control:focus {
      border-color: #38bdf8;
      box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    }
    .form-floating label {
      color: #94a3b8;
    }
    .btn-primary {
      background-color: #38bdf8;
      border: none;
    }
    .btn-primary:hover {
      background-color: #0ea5e9;
    }
    .alert-info {
      background-color: #1e3a8a;
      border-color: #3b82f6;
      color: #e0f2fe;
    }
    .alert-danger {
      background-color: #1e293b;
      border-color: #334155;
      color: #f87171;
    }
    .alert-warning {
      background-color: #334155;
      border-color: #475569;
      color: #facc15;
    }
    .form-check-input:checked {
      background-color: #38bdf8;
      border-color: #38bdf8;
    }
    .pre-box {
      background-color: #0f172a;
      border: 1px solid #334155;
      padding: 1rem;
      border-radius: 0.5rem;
      color: #f8fafc;
    }
  </style>
</head>
<body>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">

      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger mb-4">
        <h5><i class="fas fa-exclamation-triangle me-2"></i>Installation Errors</h5>
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
          <li><?= $error ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!defined("site_title")): ?>

      <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        Please ensure <strong>all fields are filled out</strong>. Blank values may cause installation errors.
      </div>

      <form method="post">
        <div class="card shadow-sm mb-4">
          <div class="card-header"><i class="fas fa-cogs me-2"></i>General Options</div>
          <div class="card-body">
            <div class="form-floating mb-3">
              <input type="text" name="site_title" class="form-control" id="site_title" required>
              <label for="site_title">Website Title</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" name="web_root" class="form-control" id="web_root" value="<?= $normalizedRoot ?>" required>
              <label for="web_root">Root Directory</label>
            </div>
          </div>
        </div>

        <div class="card shadow-sm mb-4">
          <div class="card-header"><i class="fas fa-database me-2"></i>Database Setup</div>
          <div class="card-body">
            <div class="form-floating mb-3">
              <input type="text" name="MYSQL_HOST" class="form-control" id="MYSQL_HOST" value="localhost" required>
              <label for="MYSQL_HOST">MySQL Host</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" name="MYSQL_DATABASE" class="form-control" id="MYSQL_DATABASE" required>
              <label for="MYSQL_DATABASE">Database Name</label>
            </div>
            <div class="form-floating mb-3">
              <input type="text" name="MYSQL_USERNAME" class="form-control" id="MYSQL_USERNAME" required>
              <label for="MYSQL_USERNAME">Username</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" name="MYSQL_PASSWORD" class="form-control" id="MYSQL_PASSWORD">
              <label for="MYSQL_PASSWORD">Password</label>
            </div>
          </div>
        </div>

        <div class="card shadow-sm mb-4">
          <div class="card-header"><i class="fas fa-user-lock me-2"></i>User Configuration</div>
          <div class="card-body">
            <div class="alert alert-warning mb-3">
              <i class="fas fa-info-circle me-2"></i>This user is disabled by default. Use only if necessary.
            </div>
            <div class="form-floating mb-3">
              <input type="text" name="admin_username" class="form-control" id="admin_username">
              <label for="admin_username">Root Username</label>
            </div>
            <div class="form-floating mb-3">
              <input type="password" name="admin_password" class="form-control" id="admin_password">
              <label for="admin_password">Root Password</label>
            </div>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="auto_delete_install" name="auto_delete_install" value="1">
              <label class="form-check-label" for="auto_delete_install">
                Auto-delete install folder
              </label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-download me-1"></i>Install</button>
          </div>
        </div>
      </form>

      <?php else: ?>

      <div class="card border-success shadow-sm mb-4">
        <div class="card-header"><i class="fas fa-check-circle me-2"></i>Installation Complete</div>
        <div class="card-body">
          <p>Your website is now configured. Use the credentials below to log in:</p>
          <div class="pre-box">
Username: <span class="text-info">TestUser</span><br>
Password: <span class="text-info">pass</span>
          </div>
          <p class="text-danger mt-3 fw-bold">Please delete the <code>install</code> folder immediately.</p>
          <div class="mt-3">
            <a href="../" class="btn btn-success me-2"><i class="fas fa-home me-1"></i>Home</a>
            <a href="../admin" class="btn btn-success"><i class="fas fa-user-shield me-1"></i>Admin</a>
          </div>
        </div>
      </div>

      <?php endif; ?>
    </div>
  </div>
</div>

<script src="../public/js/bootstrap.bundle.min.js"></script>
</body>
</html>