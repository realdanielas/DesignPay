<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] != '2') {
    header("Location: ../dashboard.php");
    exit();
}
?>
