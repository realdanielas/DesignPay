<?php
include("../conexion.php");
session_start();

// Solo el administrador puede eliminar usuarios
if ($_SESSION['tipo_usuario'] != '2') {
    exit("Acceso denegado");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "ID inválido.";
    exit;
}

// Evitar que el administrador elimine su propio usuario
if ($_SESSION['id_usuario'] == $id) {
    echo "No puedes eliminar tu propio usuario.";
    exit;
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "ok";
} else {
    echo "Error al eliminar el usuario.";
}

$stmt->close();
$conn->close();
