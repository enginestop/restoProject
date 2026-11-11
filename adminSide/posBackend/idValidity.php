<?php require_once __DIR__ . '/../../common/bootstrap.php'; ?>
<?php
require_once __DIR__ . '/checkIfLoggedIn.php';
require_once('../config.php');
$currentStaff = current_staff($link);
$currentStaffId = $currentStaff['staff_id'] ?? "";
?>
<?php include __DIR__ . '/../inc/dashHeader.php'; ?><div class="form-panel"><?php ui_heading('Check Staff Member Reservation Validity','Confirm customer references before choosing a payment method.','Payment'); ?><section class="surface"><p class="info-note">Leave Member ID or Reservation ID empty when they do not apply to this bill.</p><form method="post"><?= csrf_field() ?><div class="form-grid"><?php ui_field('staffId','Staff ID',['id'=>'staffId','value'=>$currentStaffId,'readonly'=>true,'full'=>true]); ui_field('memberId','Member ID',['id'=>'memberId','type'=>'number','min'=>1,'required'=>false,'hint'=>'Optional for walk-in customers.']); ui_field('reservationId','Reservation ID',['id'=>'reservationId','type'=>'number','min'=>1,'required'=>false,'hint'=>'Must match the table and bill date.']); ?></div><?php ui_actions('Check Validity','posTable.php','Back to tables'); ?></form></section><?php if($_SERVER['REQUEST_METHOD']==='POST'): ?><section class="surface section-block" aria-live="polite"><h2>Choose payment method</h2><?php     if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $staffId = (int)$currentStaffId;
        $memberId = optional_id($_POST['memberId'] ?? null);
        $reservationId = optional_id($_POST['reservationId'] ?? null);
        $bill_id = intval($_GET['bill_id']);

        $stmt = mysqli_prepare($link, "SELECT * FROM Staffs WHERE staff_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $staffId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $staffExists = mysqli_num_rows($result) > 0;
        mysqli_stmt_close($stmt);

        $memberExists = true;
        if ($memberId !== null) {
            $stmt2 = mysqli_prepare($link, "SELECT * FROM Memberships WHERE member_id = ?");
            mysqli_stmt_bind_param($stmt2, "i", $memberId);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);
            $memberExists = mysqli_num_rows($result2) > 0;
            mysqli_stmt_close($stmt2);
        }

        $reservationExists = true;
        if ($reservationId !== null) {
            $stmt3 = mysqli_prepare($link, "SELECT * FROM Reservations WHERE reservation_id = ?");
            mysqli_stmt_bind_param($stmt3, "i", $reservationId);
            mysqli_stmt_execute($stmt3);
            $result3 = mysqli_stmt_get_result($stmt3);
            $reservationExists = mysqli_num_rows($result3) > 0;
            mysqli_stmt_close($stmt3);
        }

        if ($staffExists && $memberExists && $reservationExists) {
            echo '<div class="alert alert-success" role="alert">';
            echo "Staff, member, and reservation are valid.";
            echo '</div>';
            echo '<div class="toolbar-actions">';
            $memberParam = $memberId ?? '';
            $reservationParam = $reservationId ?? '';
            echo '<a href="posCashPayment.php?bill_id=' . h($bill_id) . '&staff_id=' . h($staffId) . '&member_id=' . h($memberParam) . '&reservation_id=' . h($reservationParam) . '" class="btn btn-primary">Cash</a>';
            echo '<a href="posCardPayment.php?bill_id=' . h($bill_id) . '&staff_id=' . h($staffId) . '&member_id=' . h($memberParam) . '&reservation_id=' . h($reservationParam) . '" class="btn btn-secondary">Credit Card</a>';
            echo '</div>';
        } else {
            echo "Invalid staff, member, or reservation.";
        }
    }
 ?></section><?php endif; ?></div><?php include __DIR__ . '/../inc/dashFooter.php'; ?>
