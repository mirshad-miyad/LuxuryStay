-- Fix Admin Password
-- This updates the admin password hash to match "password" (used in demo accounts)
-- The hash below is the bcrypt hash of "password"

UPDATE admins 
SET password = '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36MMrOH2'
WHERE email = 'admin@luxurystay.lk';
