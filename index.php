<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: pages/dashboard.php');
} else {
    header('Location: pages/home.php');
}
exit;