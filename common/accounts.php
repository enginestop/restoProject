<?php
require_once __DIR__ . '/database.php';
function create_identity(array $input, string $kind): int {
    $email = strtolower(text_input($input['email'] ?? '', 'email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) problem('Invalid email.');
    $phone = text_input($input['phone_number'] ?? '', 'phone number', 30);
    if (!preg_match('/^\+?[0-9 ()-]{6,30}$/D', $phone)) problem('Invalid phone number.');
    $password = text_input($input['password'] ?? '', 'password', 72);
    if (strlen($password) < 6 || strlen($password) > 72) problem('Password must contain 6 to 72 bytes.');
    $date = $input['register_date'] ?? date('Y-m-d');
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$parsed || $parsed->format('Y-m-d') !== $date || $date > date('Y-m-d')) problem('Invalid registration date.');
    $lock = 'restaurant:email:' . substr(hash('sha256', $email),0,40);
    if ((int)one('SELECT GET_LOCK(?, 5) AS acquired', [$lock])['acquired'] !== 1) problem('Please retry registration.',409);
    try {
        return transaction(function () use ($input, $kind, $email, $phone, $password, $date) {
            global $link;
            if (one('SELECT account_id FROM Accounts WHERE email=?',[$email])) problem('This email is already registered.',409);
            db('INSERT INTO Accounts (email,register_date,phone_number,password) VALUES (?,?,?,?)',[$email,$date,$phone,password_hash($password,PASSWORD_DEFAULT)]);
            $account = $link->insert_id;
            if ($kind === 'member') {
                $name=text_input($input['member_name'] ?? '', 'member name');
                $points=filter_var($input['points'] ?? '0', FILTER_VALIDATE_INT, ['options'=>['min_range'=>0,'max_range'=>10000000]]);
                if ($points===false) problem('Invalid membership points.');
                db('INSERT INTO Memberships (member_name,points,account_id) VALUES (?,?,?)',[$name,$points,$account]);
            } elseif ($kind === 'staff') {
                $name=text_input($input['staff_name'] ?? '', 'staff name');
                $role=text_input($input['role'] ?? '', 'role');
                if (!in_array($role,['Waiter','Chef','Manager'],true)) problem('Role must be Waiter, Chef or Manager.');
                db('INSERT INTO Staffs (staff_name,role,account_id) VALUES (?,?,?)',[$name,$role,$account]);
            }
            return $account;
        });
    } finally { db('SELECT RELEASE_LOCK(?)',[$lock]); }
}
function menu_input(array $input): array {
    $id=text_input($input['item_id'] ?? '', 'item ID',6);
    if (!preg_match('/^[a-zA-Z0-9]{1,6}$/D',$id)) problem('Item ID must contain 1 to 6 letters or digits.');
    $name=text_input($input['item_name'] ?? '', 'item name');
    $type=text_input($input['item_type'] ?? '', 'item type');
    $category=text_input($input['item_category'] ?? '', 'category');
    if (!in_array($category,['Main Dishes','Side Snacks','Drinks'],true)) problem('Category must be Main Dishes, Side Snacks or Drinks.');
    $price=cents($input['item_price'] ?? '');
    if ($price<=0) problem('Price must be positive.');
    return [$id,$name,$type,$category,money($price),text_input($input['item_description'] ?? '', 'description',255,false)];
}
