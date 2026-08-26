<?php
require_once __DIR__ . '/includes/auth.php';
logoutUser();
flash('success', 'You have been logged out successfully.');
redirect(APP_URL . '/index.php');
