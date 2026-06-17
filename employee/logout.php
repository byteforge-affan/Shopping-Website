<?php session_start(); unset($_SESSION['emp_id'],$_SESSION['emp_name']); header('Location: login.php'); exit; ?>
