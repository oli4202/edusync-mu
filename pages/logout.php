<?php
// LEGACY FILE - REDIRECT TO MVC
header('Location: /logout');
exit;

require_once __DIR__ . '/../includes/auth.php';
logoutUser();
