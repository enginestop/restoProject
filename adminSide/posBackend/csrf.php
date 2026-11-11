<?php
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        problem('Invalid CSRF token.', 403);
    }
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function current_staff(mysqli $link): ?array
{
    $accountId = intval($_SESSION['logged_account_id'] ?? 0);
    if ($accountId <= 0) {
        return null;
    }

    $stmt = $link->prepare("SELECT staff_id, staff_name, role, account_id FROM Staffs WHERE account_id = ?");
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    return $staff ?: null;
}

function require_staff_role(mysqli $link, array $allowedRoles): array
{
    $staff = current_staff($link);
    $role = strtolower((string) ($staff['role'] ?? ''));
    $allowed = array_map('strtolower', $allowedRoles);

    if (!$staff || !in_array($role, $allowed, true)) {
        problem('Insufficient staff privileges.', 403);
    }

    return $staff;
}

function require_manager_password(mysqli $link): void
{
    $accountId = intval($_POST['admin_id'] ?? 0);
    $password = $_POST['password'] ?? '';

    $stmt = $link->prepare(
        "SELECT a.password, s.role
         FROM Accounts a
         INNER JOIN Staffs s ON s.account_id = a.account_id
         WHERE a.account_id = ?"
    );
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$row || strcasecmp((string) $row['role'], 'Manager') !== 0 || !password_verify($password, $row['password'])) {
        problem('Manager credentials are required.', 403);
    }
}
