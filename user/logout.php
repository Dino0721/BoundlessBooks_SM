<?php

require '../pageFormat/base.php';
require_once __DIR__ . '/../app/bootstrap.php';

$auth = new AuthService();
$auth->logout();

// Note: Session is destroyed, so temp() message might not persist unless we handle it differently.
// For now, we just redirect.
redirect('login.php');