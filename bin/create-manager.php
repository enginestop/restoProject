<?php
// Create the first Manager interactively; this command is never available over HTTP.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function setup_prompt(string $label, bool $secret = false): string {
    fwrite(STDOUT, $label . ': ');
    $hidden = $secret && stream_isatty(STDIN);
    if ($hidden) {
        system('stty -echo', $status);
        if ($status !== 0) throw new RuntimeException('Unable to hide password input.');
    }
    try {
        $value = fgets(STDIN);
    } finally {
        if ($hidden) {
            system('stty echo');
            fwrite(STDOUT, PHP_EOL);
        }
    }
    if ($value === false) throw new RuntimeException('Input cancelled.');
    return trim($value);
}

try {
    require_once __DIR__ . '/../common/accounts.php';
    if (one("SELECT staff_id FROM Staffs WHERE role='Manager' LIMIT 1")) {
        throw new RuntimeException('A Manager already exists. Use Staff > Add Staff to create further staff accounts.');
    }
    $name = setup_prompt('Manager name');
    $email = setup_prompt('Email');
    $phone = setup_prompt('Phone number');
    $password = setup_prompt('Password', true);
    if ($password !== setup_prompt('Confirm password', true)) {
        throw new RuntimeException('Passwords do not match.');
    }
    $id = create_identity([
        'staff_name' => $name,
        'role' => 'Manager',
        'email' => $email,
        'phone_number' => $phone,
        'password' => $password,
        'register_date' => date('Y-m-d'),
    ], 'staff');
    fwrite(STDOUT, "Manager created. Staff Account ID for login: $id" . PHP_EOL);
} catch (Throwable $error) {
    fwrite(STDERR, 'Setup failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
