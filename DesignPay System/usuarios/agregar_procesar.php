<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $tipo_usuario = $_POST['tipo_usuario'];

    if (empty($usuario) || empty($password) || empty($tipo_usuario)) {
        echo 'Todos los campos son obligatorios.';
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo 'El nombre de usuario ya existe.';
        exit;
    }
    $stmt->close();

    $hashed_password = hash('sha256', $password);

    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, tipo_usuario) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $usuario, $hashed_password, $tipo_usuario);

    if ($stmt->execute()) {
        echo 'ok'; // El fetch captará esto
    } else {
        echo 'Error al guardar el usuario en la base de datos.';
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Acceso inválido.';
    exit;
}
