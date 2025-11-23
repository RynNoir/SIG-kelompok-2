<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
session_destroy();
header('Location: login.php');
exit;
?>