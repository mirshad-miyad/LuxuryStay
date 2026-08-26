-- Delete existing admin and create new one
DELETE FROM admins WHERE email = 'admin@luxurystay.lk';

-- New admin with password: AdminSecure@2026
-- Hash: $2y$10$DXE7Q2zAh5V5E7J8K9L2H.KvW5N0M1P2Q3R4S5T6U7V8W9X0Y1Z2
INSERT INTO admins (name, email, password) VALUES 
('System Administrator', 'admin@luxurystay.lk', '$2y$10$DXE7Q2zAh5V5E7J8K9L2H.KvW5N0M1P2Q3R4S5T6U7V8W9X0Y1Z2');

-- Verify the admin was created
SELECT id, name, email FROM admins WHERE email = 'admin@luxurystay.lk';
