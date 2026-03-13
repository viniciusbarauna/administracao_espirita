<?php
session_start();


if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_nome'])) {

    header("Location: login.php");
    exit;
}
?>