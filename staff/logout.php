<?php
// staff/logout.php
session_start();
unset($_SESSION['staff_logged_in']);
header('Location: login.php');
exit;