<?php
session_start();
include("conexion.php");

// Validar datos enviados
if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
    header("Location: login.php?error=1");
    exit();
}

$usuario = $_POST['usuario'];
$password = hash('sha256', $_POST['password']);

// Consultar usuario
$sql = "SELECT * FROM usuarios WHERE usuario = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $usuario, $password);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    // Usuario válido, guardar sesión
    $user = $resultado->fetch_assoc();
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
    $_SESSION['id_usuario'] = $user['id'];

    header("Location: dashboard.php");
    exit();
} else {
    // Usuario inválido → redirigir con error
    header("Location: login.php?error=1");
    exit();
}
?>

