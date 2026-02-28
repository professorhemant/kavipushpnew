<?php
// Load WordPress from its own directory — ensures correct cookie paths and constants
require_once __DIR__ . '/wp-load.php';

$error = '';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    wp_logout();
    wp_redirect(home_url('/login.php'));
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_id'], $_POST['password'])) {
    $login_id = sanitize_text_field(trim($_POST['login_id']));
    $password  = $_POST['password'];

    $creds = array(
        'user_login'    => $login_id,
        'user_password' => $password,
        'remember'      => true,
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $error = $user->get_error_message(); // show exact WP error for diagnosis
    } else {
        wp_redirect(home_url('/'));
        exit;
    }
}

// Check current login state
$already_logged_in  = is_user_logged_in();
$current_user_email = $already_logged_in ? wp_get_current_user()->user_email : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container {
      background: rgba(255,255,255,0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 16px;
      padding: 48px 40px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.4);
    }

    .login-title {
      text-align: center;
      color: #fff;
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .login-subtitle {
      text-align: center;
      color: rgba(255,255,255,0.5);
      font-size: 14px;
      margin-bottom: 36px;
    }

    .form-group { margin-bottom: 20px; }

    label {
      display: block;
      color: rgba(255,255,255,0.8);
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 8px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px 16px;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 8px;
      color: #fff;
      font-size: 15px;
      outline: none;
      transition: border-color 0.2s, background 0.2s;
    }

    input::placeholder { color: rgba(255,255,255,0.3); }

    input:focus {
      border-color: #4f8ef7;
      background: rgba(255,255,255,0.12);
    }

    .error-msg {
      background: rgba(255,80,80,0.15);
      border: 1px solid rgba(255,80,80,0.4);
      color: #ff6b6b;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 13px;
      margin-bottom: 20px;
      text-align: center;
    }

    .btn {
      width: 100%;
      padding: 13px;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.1s;
      letter-spacing: 0.5px;
      margin-top: 4px;
      display: block;
      text-align: center;
      text-decoration: none;
    }

    .btn:hover { opacity: 0.9; }
    .btn:active { transform: scale(0.98); }

    .btn-blue   { background: linear-gradient(135deg, #4f8ef7, #3a6fd8); }
    .btn-green  { background: linear-gradient(135deg, #27ae60, #1e8449); margin-top: 0; }
    .btn-red    { background: linear-gradient(135deg, #e05252, #c0392b); margin-top: 8px; }
    .btn-ghost  {
      background: transparent;
      border: 1px solid rgba(255,255,255,0.25);
      color: rgba(255,255,255,0.7);
      margin-top: 12px;
      font-size: 15px;
      font-weight: 500;
    }
    .btn-ghost:hover {
      border-color: rgba(255,255,255,0.5);
      color: #fff;
      background: rgba(255,255,255,0.05);
      opacity: 1;
    }

    .logged-in-info {
      text-align: center;
      color: rgba(255,255,255,0.6);
      font-size: 13px;
      margin-bottom: 16px;
      padding: 10px 14px;
      background: rgba(79,200,120,0.1);
      border: 1px solid rgba(79,200,120,0.3);
      border-radius: 8px;
      word-break: break-all;
    }

    .logged-in-info span {
      color: #6ee09e;
      font-weight: 600;
      display: block;
      margin-top: 2px;
    }

    /* Modal */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(4px);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal {
      background: #1a1a2e;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 14px;
      padding: 36px 32px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.6);
      position: relative;
    }

    .modal-title {
      text-align: center;
      color: #fff;
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 6px;
    }

    .modal-subtitle {
      text-align: center;
      color: rgba(255,255,255,0.45);
      font-size: 13px;
      margin-bottom: 28px;
    }

    .modal-close {
      position: absolute;
      top: 14px; right: 16px;
      background: none;
      border: none;
      color: rgba(255,255,255,0.4);
      font-size: 20px;
      cursor: pointer;
    }
    .modal-close:hover { color: #fff; }
  </style>
</head>
<body>

<div class="login-container">
  <h1 class="login-title">Welcome Back</h1>
  <p class="login-subtitle">Please sign in to continue</p>

  <?php if ($already_logged_in): ?>

    <div class="logged-in-info">
      Signed in as<span><?php echo esc_html($current_user_email); ?></span>
    </div>
    <a class="btn btn-green" href="<?php echo esc_url(home_url('/')); ?>">Go to Dashboard</a>
    <a class="btn btn-red" href="<?php echo esc_url(home_url('/login.php?action=logout')); ?>">Logout</a>

  <?php else: ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="login_id">Login ID</label>
        <input
          type="text"
          id="login_id"
          name="login_id"
          placeholder="Enter your email"
          autocomplete="username"
          required
        />
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Enter your password"
          autocomplete="current-password"
          required
        />
      </div>

      <?php if ($error): ?>
        <div class="error-msg"><?php echo esc_html($error); ?></div>
      <?php endif; ?>

      <button type="submit" class="btn btn-blue">Login</button>
    </form>

  <?php endif; ?>

  <button class="btn btn-ghost" onclick="openAdminModal()">Admin Panel</button>
</div>

<!-- Admin Login Modal -->
<div class="modal-overlay" id="adminModal">
  <div class="modal">
    <button class="modal-close" onclick="closeAdminModal()">&times;</button>
    <h2 class="modal-title">Admin Panel</h2>
    <p class="modal-subtitle">Enter your admin credentials</p>

    <form id="adminForm" onsubmit="handleAdminLogin(event)">
      <div class="form-group">
        <label for="adminId">User ID</label>
        <input type="text" id="adminId" placeholder="Enter your email" required />
      </div>
      <div class="form-group">
        <label for="adminPassword">Password</label>
        <input type="password" id="adminPassword" placeholder="Enter your password" required />
      </div>
      <div class="error-msg" id="adminErrorMsg" style="display:none;">
        Invalid credentials. Access denied.
      </div>
      <button type="submit" class="btn btn-blue">Admin Login</button>
    </form>
  </div>
</div>

<script>
  const VALID_ID = 'prof.hemant.sgnr@gmail.com';
  const VALID_PASS = 'bahu2624@';

  function openAdminModal() {
    document.getElementById('adminModal').classList.add('open');
    document.getElementById('adminId').focus();
  }

  function closeAdminModal() {
    document.getElementById('adminModal').classList.remove('open');
    document.getElementById('adminForm').reset();
    document.getElementById('adminErrorMsg').style.display = 'none';
  }

  document.getElementById('adminModal').addEventListener('click', function(e) {
    if (e.target === this) closeAdminModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAdminModal();
  });

  function handleAdminLogin(event) {
    event.preventDefault();
    const id   = document.getElementById('adminId').value.trim();
    const pass = document.getElementById('adminPassword').value;
    const err  = document.getElementById('adminErrorMsg');

    if (id === VALID_ID && pass === VALID_PASS) {
      err.style.display = 'none';
      window.location.href = '<?php echo esc_url(admin_url("admin.php?page=kavipushp-dashboard")); ?>';
    } else {
      err.style.display = 'block';
      document.getElementById('adminPassword').value = '';
      document.getElementById('adminPassword').focus();
    }
  }
</script>

</body>
</html>
