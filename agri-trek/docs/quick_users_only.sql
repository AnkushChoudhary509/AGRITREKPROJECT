-- ============================================================
-- Agri-Trek: Insert Users Only (run AFTER migrate + db:seed fails)
-- OR: run this in phpMyAdmin if you get "Invalid email/password"
-- ============================================================
USE agritrek;

-- Remove old users first to avoid duplicates
DELETE FROM users WHERE email IN ('admin@agritrek.com','farmer@agritrek.com');

-- Insert Admin (password = "password")
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES ('Admin User', 'admin@agritrek.com', '$2y$10$Wt8/1wVqUnRa7z3jDm5SZeXB/j/0VYE24zLBbPngNgAHXSiHkWe/y', 'admin', NOW(), NOW());

-- Insert Farmer (password = "password")
INSERT INTO users (name, email, password, role, farmer_id, created_at, updated_at)
VALUES ('Ramesh Patel', 'farmer@agritrek.com', '$2y$10$Wt8/1wVqUnRa7z3jDm5SZeXB/j/0VYE24zLBbPngNgAHXSiHkWe/y', 'farmer', 1, NOW(), NOW());

-- Verify
SELECT id, name, email, role FROM users;
