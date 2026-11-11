ALTER TABLE Accounts ADD KEY idx_email (email);
ALTER TABLE Staffs ADD UNIQUE KEY uniq_staff_account (account_id);
ALTER TABLE Memberships ADD UNIQUE KEY uniq_member_account (account_id);
ALTER TABLE Reservations
  DROP INDEX uniq_reservation_slot,
  ADD account_id INT NULL,
  ADD owner_token CHAR(64) NULL,
  ADD cancelled_at DATETIME NULL,
  ADD active_slot TINYINT GENERATED ALWAYS AS (IF(cancelled_at IS NULL, 1, NULL)) STORED,
  ADD UNIQUE KEY uniq_active_slot (table_id, reservation_date, reservation_time, active_slot),
  ADD FOREIGN KEY (account_id) REFERENCES Accounts(account_id);
ALTER TABLE Bills
  ADD subtotal DECIMAL(10,2) NULL,
  ADD tax_amount DECIMAL(10,2) NULL,
  ADD total_amount DECIMAL(10,2) NULL,
  ADD tendered_amount DECIMAL(10,2) NULL,
  ADD change_amount DECIMAL(10,2) NULL,
  ADD active_table_id INT GENERATED ALWAYS AS (IF(payment_time IS NULL, table_id, NULL)) STORED,
  ADD UNIQUE KEY uniq_active_bill_table (active_table_id);
ALTER TABLE Bill_Items
  ADD unit_price DECIMAL(10,2) NULL,
  ADD item_name_snapshot VARCHAR(255) NULL,
  ADD category_snapshot VARCHAR(255) NULL;
UPDATE Bill_Items bi JOIN Menu m ON m.item_id=bi.item_id SET bi.unit_price=m.item_price, bi.item_name_snapshot=m.item_name, bi.category_snapshot=m.item_category;
ALTER TABLE Bill_Items MODIFY unit_price DECIMAL(10,2) NOT NULL, MODIFY item_name_snapshot VARCHAR(255) NOT NULL, MODIFY category_snapshot VARCHAR(255) NOT NULL, ADD CHECK (quantity BETWEEN 1 AND 1000), ADD CHECK (unit_price > 0);
ALTER TABLE Kitchen ADD bill_item_id INT NULL, ADD FOREIGN KEY (bill_item_id) REFERENCES Bill_Items(bill_item_id);
UPDATE Bills b JOIN (SELECT bill_id, SUM(unit_price * quantity) AS subtotal FROM Bill_Items GROUP BY bill_id) t ON b.bill_id=t.bill_id SET b.subtotal=t.subtotal, b.tax_amount=ROUND(t.subtotal*0.1,2), b.total_amount=t.subtotal+ROUND(t.subtotal*0.1,2) WHERE b.payment_time IS NOT NULL;
ALTER TABLE Restaurant_Tables ADD CHECK (capacity BETWEEN 1 AND 1000);
ALTER TABLE Memberships ADD CHECK (points >= 0);
