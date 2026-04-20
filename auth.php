<?php
session_start();

function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_role(int $roleId): void
{
    require_login();
    if (!isset($_SESSION['role_id']) || (int)$_SESSION['role_id'] !== $roleId) {
        header("Location: login.php");
        exit;
    }
}

function require_roles(array $roleIds): void
{
    require_login();
    $currentRole = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0;
    if (!in_array($currentRole, $roleIds, true)) {
        header("Location: login.php");
        exit;
    }
}

function current_user_name(): string
{
    return isset($_SESSION['username']) ? (string)$_SESSION['username'] : 'Guest';
}
?>
