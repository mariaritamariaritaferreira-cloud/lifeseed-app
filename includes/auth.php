<?php
function startAppSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void
{
    startAppSession();
    if (!isLoggedIn()) {
        header('Location: /?page=login');
        exit;
    }
}

function setFlash(string $message, string $type = 'success'): void
{
    startAppSession();
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function getFlash(): ?array
{
    startAppSession();
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function loginUser(int $userId, string $name): void
{
    startAppSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
}

function logoutUser(): void
{
    startAppSession();
    $_SESSION = [];
    session_destroy();
}

function currentUser(): ?array
{
    startAppSession();
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch();
}
