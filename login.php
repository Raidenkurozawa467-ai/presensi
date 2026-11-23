<?php
require __DIR__ . '/includes/bootstrap.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $user = (new User())->authenticate($username, $password);
        if ($user !== null) {
            session_regenerate_id(true);
            $_SESSION['id_user'] = (int) $user['id_user'];
            $_SESSION['username'] = $user['username'];
            redirect('index.php');
        }
        $error = 'Username atau password salah.';
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-box card">
    <h1>Login Administrator</h1>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= e($username) ?>" autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <button type="submit" class="btn btn-block">Masuk</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
