<?php
include("../verificar_admin.php");
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "ok";
        } else {
            echo "Error al eliminar producto.";
        }
    } else {
        echo "ID inválido.";
    }
} else {
    echo "Método no permitido.";
}
exit();
?>
