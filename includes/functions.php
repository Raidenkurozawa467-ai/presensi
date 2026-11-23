<?php
declare(strict_types=1);

/** Escape semua output ke HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['id_user'], $_SESSION['username']);
}

/** Semua halaman selain login wajib memanggil ini. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function showFlash(): void
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $cls = $f['type'] === 'success' ? 'alert-success' : 'alert-error';
        echo '<div class="alert ' . $cls . '" role="alert">' . e($f['message']) . '</div>';
    }
}

/** Pesan error per kolom di bawah input. */
function fieldError(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<small class="field-error">' . e($errors[$key]) . '</small>' : '';
}

function formatWaktu(string $datetime): string
{
    return date('d/m/Y H:i:s', strtotime($datetime));
}
