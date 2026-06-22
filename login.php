<?php
require_once __DIR__ . "/../includes/koneksi.php";
require_once __DIR__ . "/../includes/functions.php";

$title = "Login - Foodie";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $username = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username=? LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();

  if (!$user || !password_verify($pass, $user['password_hash'])) {
    $err = "Username atau password salah.";
  } else {
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    header("Location: /ProyekWeb/index.php");
    exit;
  }
}

include __DIR__ . "/../includes/header.php";
?>
<div class="form">
  <h2>Login</h2>
  <p class="muted">Masuk untuk tambah resep, rating, dan komentar.</p>

  <?php if (!empty($_GET['ok'])): ?>
    <p class="badge green">Registrasi berhasil. Silakan login.</p>
  <?php endif; ?>

  <?php if ($err): ?><p class="badge" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><?= h($err) ?></p><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label>Username</label>
    <input name="username" required value="<?= h($_POST['username'] ?? '') ?>"/>

    <label>Password</label>
    <input type="password" name="password" required />

    <div class="actions">
      <button class="btn" type="submit">Login</button>
      <a class="btn ghost" href="/ProyekWeb/auth/register.php">Buat akun</a>
    </div>
  </form>

  <div class="hr"></div>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
