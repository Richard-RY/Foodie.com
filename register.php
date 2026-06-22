<?php
require_once __DIR__ . "/../includes/koneksi.php";
require_once __DIR__ . "/../includes/functions.php";

$title = "Register - Foodie";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $username = trim($_POST['username'] ?? '');
  $pass = $_POST['password'] ?? '';

  if ($username === '' || $pass === '') $err = "Username & password wajib diisi.";
  elseif (strlen($username) < 3) $err = "Username minimal 3 karakter.";
  elseif (strlen($pass) < 6) $err = "Password minimal 6 karakter.";
  else {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'member')");
    $stmt->bind_param("ss", $username, $hash);
    if ($stmt->execute()) {
      header("Location: /ProyekWeb/auth/login.php?ok=1");
      exit;
    } else {
      $err = "Username sudah dipakai.";
    }
  }
}

include __DIR__ . "/../includes/header.php";
?>
<div class="form">
  <h2>Register</h2>
  <p class="muted">Buat akun member untuk menambahkan resep.</p>
  <?php if ($err): ?><p class="badge" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><?= h($err) ?></p><?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
    <label>Username</label>
    <input name="username" required placeholder="contoh: foodie_andi" value="<?= h($_POST['username'] ?? '') ?>" />

    <label>Password</label>
    <input type="password" name="password" required placeholder="minimal 6 karakter" />

    <div class="actions">
      <button class="btn" type="submit">Daftar</button>
      <a class="btn ghost" href="/ProyekWeb/auth/login.php">Sudah punya akun</a>
    </div>
  </form>
</div>
<?php include __DIR__ . "/../includes/footer.php"; ?>
