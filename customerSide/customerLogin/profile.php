<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
// Include your database connection code here
require_once '../config.php';

// Check if the user is logged in
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to the login page if not logged in
    exit;
}

// Fetch the user's profile information
$user_id = $_SESSION['account_id'];

$query = "SELECT m.member_name, m.points, a.email, a.phone_number, a.register_date
          FROM Memberships AS m
          INNER JOIN Accounts AS a ON m.account_id = a.account_id
          WHERE m.account_id = ?";

$stmt = $link->prepare($query);
$stmt->bind_param('i', $user_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
} else {
    echo 'Error: ' . $stmt->error;
    // Handle the error gracefully
}

$stmt->close();
$link->close();
?>

<?php require_once __DIR__ . '/../../common/ui.php'; ui_start('Your profile','Your account details and membership points.',true); ?><div class="info-note">Welcome, <?= h($row['member_name'] ?? '') ?>!</div><div class="metric-grid"><div class="metric"><p>Membership points</p><strong><?= h($row['points'] ?? 0) ?></strong></div><div class="metric"><p>Member status</p><strong><?= (int)($row['points'] ?? 0)>=1000?'VIP':'Regular' ?></strong></div><div class="metric"><p>Member since</p><strong><?= h($row['register_date'] ?? '') ?></strong></div></div><dl class="definition-list"><div><dt>Member name</dt><dd><?= h($row['member_name'] ?? '') ?></dd></div><div><dt>Email</dt><dd><?= h($row['email'] ?? '') ?></dd></div><div><dt>Phone number</dt><dd><?= h($row['phone_number'] ?? '') ?></dd></div></dl><div class="form-actions"><a class="btn btn-primary" href="../CustomerReservation/reservePage.php">Reservations</a><a class="btn btn-secondary" href="reset-password.php">Change password</a><a class="btn btn-secondary" href="logout.php">Logout</a></div><?php ui_end(); ?>
