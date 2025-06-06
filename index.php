<?php
require 'inc/_global/config.php';
require 'inc/_global/login_check.php';

// Redirect everyone (who has passed the login check) straight to dashboard.php
header('Location: dashboard.php');
exit;
?>