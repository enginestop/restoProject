<?php
require_once __DIR__ . '/database.php';
function lock_bill(int $id, ?int $table = null, bool $unpaid = true): array {
    $bill = one('SELECT * FROM Bills WHERE bill_id = ? FOR UPDATE', [$id]);
    if (!$bill) problem('Bill not found.', 404);
    if ($table !== null && (int)$bill['table_id'] !== $table) problem('Bill does not belong to this table.');
    if ($unpaid && $bill['payment_time'] !== null) problem('Bill has already been paid.', 409);
    return $bill;
}
function open_bill(int $table): int {
    return transaction(function () use ($table) {
        global $link;
        $row = one('SELECT * FROM Restaurant_Tables WHERE table_id = ? FOR UPDATE', [$table]);
        if (!$row || !$row['is_available']) problem('Table is unavailable.');
        $bill = one('SELECT bill_id FROM Bills WHERE table_id = ? AND payment_time IS NULL', [$table]);
        if ($bill) return (int)$bill['bill_id'];
        db('INSERT INTO Bills (table_id, bill_time) VALUES (?, NOW())', [$table]);
        return $link->insert_id;
    });
}
function add_item(int $bill, int $table, string $item, int $quantity): int {
    if ($quantity < 1 || $quantity > 1000) problem('Quantity must be an integer from 1 to 1000.');
    return transaction(function () use ($bill, $table, $item, $quantity) {
        global $link;
        lock_bill($bill, $table);
        $menu = one('SELECT * FROM Menu WHERE item_id = ? FOR SHARE', [$item]);
        if (!$menu) problem('Menu item not found.', 404);
        // Each submission is a separate line and kitchen ticket, including after a prior ticket is finished.
        db('INSERT INTO Bill_Items (bill_id, item_id, quantity, unit_price, item_name_snapshot, category_snapshot) VALUES (?, ?, ?, ?, ?, ?)',
            [$bill, $item, $quantity, $menu['item_price'], $menu['item_name'], $menu['item_category']]);
        $line = $link->insert_id;
        db('INSERT INTO Kitchen (table_id, item_id, quantity, time_submitted, bill_item_id) VALUES (?, ?, ?, NOW(), ?)', [$table, $item, $quantity, $line]);
        return $line;
    });
}
function remove_item(int $bill, int $table, int $line): void {
    transaction(function () use ($bill, $table, $line) {
        lock_bill($bill, $table);
        if (!one('SELECT bill_item_id FROM Bill_Items WHERE bill_item_id = ? AND bill_id = ?', [$line, $bill])) problem('Bill item not found.', 404);
        db('DELETE FROM Kitchen WHERE bill_item_id = ?', [$line]);
        db('DELETE FROM Bill_Items WHERE bill_item_id = ? AND bill_id = ?', [$line, $bill]);
    });
}
function bill_totals(int $bill): array {
    $sum = one('SELECT COALESCE(SUM(quantity * unit_price), 0) AS subtotal FROM Bill_Items WHERE bill_id = ?', [$bill]);
    $subtotal = cents($sum['subtotal']);
    $tax = intdiv($subtotal + 5, 10);
    return ['subtotal' => $subtotal, 'tax' => $tax, 'total' => $subtotal + $tax];
}
function pay_bill(int $billId, int $staff, ?int $member, ?int $reservation, string $method, array $payment): array {
    return transaction(function () use ($billId, $staff, $member, $reservation, $method, $payment) {
        global $link;
        $bill = lock_bill($billId, null, false);
        // A committed bill is the idempotency key; retries cannot create payments or points twice.
        if ($bill['payment_time'] !== null) return ['already_paid' => true, 'total' => cents($bill['total_amount']), 'change' => cents($bill['change_amount'] ?? '0')];
        $totals = bill_totals($billId);
        if ($totals['total'] <= 0) problem('Cannot pay an empty bill.');
        if (!one('SELECT staff_id FROM Staffs WHERE staff_id = ?', [$staff])) problem('Staff not found.');
        if ($member !== null && !one('SELECT member_id FROM Memberships WHERE member_id = ? FOR UPDATE', [$member])) problem('Member not found.');
        if ($reservation !== null) {
            $r = one('SELECT * FROM Reservations WHERE reservation_id = ? FOR SHARE', [$reservation]);
            if (!$r || (int)$r['table_id'] !== (int)$bill['table_id'] || $r['cancelled_at'] !== null || $r['reservation_date'] !== substr($bill['bill_time'], 0, 10)) problem('Reservation does not match the bill table and date.');
            if (one('SELECT bill_id FROM Bills WHERE reservation_id = ? AND payment_time IS NOT NULL', [$reservation])) problem('Reservation has already been used.', 409);
        }
        $card = null; $change = 0; $tendered = null;
        if ($method === 'cash') {
            $tendered = cents($payment['payment_amount'] ?? '');
            if ($tendered < $totals['total']) problem('Payment amount is not sufficient.');
            $change = $tendered - $totals['total'];
        } elseif ($method === 'card') {
            $name = text_input($payment['cardName'] ?? '', 'card holder');
            $number = str_replace(' ', '', $payment['cardNumber'] ?? '');
            $expiry = $payment['expiryDate'] ?? '';
            if (!preg_match('/^\d{15,19}$/D', $number) || !preg_match('/^\d{3,4}$/D', $payment['securityCode'] ?? '') || !preg_match('/^(0[1-9]|1[0-2])\/\d{4}$/D', $expiry)) problem('Invalid card details.');
            if (substr($expiry, 3) . substr($expiry, 0, 2) < date('Ym')) problem('Card has expired.');
            db('INSERT INTO card_payments (account_holder_name, card_last4, expiry_date) VALUES (?, ?, ?)', [$name, substr($number, -4), $expiry]);
            $card = $link->insert_id;
        } else problem('Invalid payment method.');
        db('UPDATE Bills SET staff_id=?, member_id=?, reservation_id=?, card_id=?, payment_method=?, payment_time=NOW(), subtotal=?, tax_amount=?, total_amount=?, tendered_amount=?, change_amount=? WHERE bill_id=?',
            [$staff, $member, $reservation, $card, $method, money($totals['subtotal']), money($totals['tax']), money($totals['total']), $tendered === null ? null : money($tendered), money($change), $billId]);
        if ($member !== null) db('UPDATE Memberships SET points = points + ? WHERE member_id = ?', [intdiv($totals['total'], 100), $member]);
        return ['already_paid' => false, 'total' => $totals['total'], 'change' => $change];
    });
}
function reservation_input(array $input): array {
    $date = $input['reservation_date'] ?? '';
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date || $date < date('Y-m-d') || $date > date('Y-m-d', strtotime('+1 year'))) problem('Select a valid reservation date within the next year.');
    $time = $input['reservation_time'] ?? '';
    if (strlen($time) === 5) $time .= ':00';
    if (!preg_match('/^(1[0-9]|20):00:00$/D', $time) || "$date $time" <= date('Y-m-d H:i:s')) problem('Select a future hourly reservation slot from 10:00 to 20:00.');
    return [$date, $time, number_id($input['head_count'] ?? '', 'head count')];
}
function reserve(array $input, ?int $account, string $owner): int {
    [$date, $time, $count] = reservation_input($input);
    $table = number_id($input['table_id'] ?? '', 'table');
    $name = text_input($input['customer_name'] ?? '', 'customer name');
    $special = text_input($input['special_request'] ?? '', 'special request', 255, false);
    return transaction(function () use ($date, $time, $count, $table, $name, $special, $account, $owner) {
        global $link;
        $row = one('SELECT * FROM Restaurant_Tables WHERE table_id = ? FOR UPDATE', [$table]);
        if (!$row || !$row['is_available'] || (int)$row['capacity'] < $count) problem('Selected table cannot fit the requested head count.');
        if (one('SELECT reservation_id FROM Reservations WHERE table_id=? AND reservation_date=? AND reservation_time=? AND cancelled_at IS NULL', [$table, $date, $time])) problem('Selected table is no longer available.', 409);
        db('INSERT INTO Reservations (customer_name, table_id, reservation_time, reservation_date, head_count, special_request, account_id, owner_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$name, $table, $time, $date, $count, $special, $account, $owner]);
        $id = $link->insert_id;
        db("INSERT INTO Table_Availability (table_id, reservation_date, reservation_time, status) VALUES (?, ?, ?, 'no')", [$table, $date, $time]);
        return $id;
    });
}
function reservation_owner(array $row): bool {
    return (!empty($_SESSION['account_id']) && (int)$row['account_id'] === (int)$_SESSION['account_id']) ||
        (!empty($row['owner_token']) && !empty($_SESSION['reservation_owner']) && hash_equals($row['owner_token'], $_SESSION['reservation_owner']));
}
function cancel_reservation(int $id): void {
    transaction(function () use ($id) {
        $r = one('SELECT * FROM Reservations WHERE reservation_id = ? FOR UPDATE', [$id]);
        if (!$r) problem('Reservation not found.', 404);
        if (one('SELECT bill_id FROM Bills WHERE reservation_id = ?', [$id])) problem('Reservation is referenced by a transaction and cannot be cancelled.', 409);
        db('DELETE FROM Table_Availability WHERE table_id=? AND reservation_date=? AND reservation_time=?', [$r['table_id'], $r['reservation_date'], $r['reservation_time']]);
        db('UPDATE Reservations SET cancelled_at = COALESCE(cancelled_at, NOW()) WHERE reservation_id = ?', [$id]);
    });
}
