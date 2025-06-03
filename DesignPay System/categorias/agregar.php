<?php
include("../verificar_admin.php");
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    if ($nombre === '') {
        echo json_encode(['error' => 'El nombre es obligatorio']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['error' => 'Error al guardar']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Agregar Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-3">
    <h2>Agregar Categoría</h2>

    <form id="formAgregar" onsubmit="guardarCategoria(event)">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la categoría</label>
            <input type="text" id="nombre" name="nombre" class="form-control" required placeholder="Nombre de la categoría" />
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <button type="button" class="btn btn-secondary" onclick="cargarContenido('categorias/index.php')">Cancelar</button>
    </form>

    <script>
        function guardarCategoria(e) {
            e.preventDefault();
            const form = document.getElementById('formAgregar');
            const formData = new FormData(form);

            fetch('categorias/agregar.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.ok){
                    Swal.fire('Guardado', 'Categoría agregada con éxito.', 'success');
                    cargarContenido('categorias/index.php');
                } else {
                    Swal.fire('Error', data.error || 'Error al guardar', 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'Error en la conexión', 'error');
            });
        }
    </script>
</body>
</html>
