<?php
include("../verificar_admin.php");
include("../conexion.php");

// Manejo POST: guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: text/plain"); // ← Muy importante: evita que retorne HTML

    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);

    $stmt = $conn->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
    $stmt->bind_param("si", $nombre, $id);

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }
    exit(); // Evita que continúe con el HTML
}

// Manejo GET: cargar el formulario
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$c = $res->fetch_assoc();
?>

<div class="container mt-4">
    <h3 class="mb-4">Editar Categoría</h3>
    <form id="form-editar-categoria" onsubmit="actualizarCategoria(event)">
        <input type="hidden" name="id" value="<?= $c['id'] ?>">

        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la categoría</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($c['nombre']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <button type="button" class="btn btn-secondary ms-2" onclick="cargarContenido('categorias/index.php')">Cancelar</button>
    </form>
</div>
<!-- Incluir SweetAlert2 si no está cargado -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script para actualizar la categoría -->
<script>
function actualizarCategoria(event) {
    event.preventDefault();
    const form = document.getElementById('form-editar-categoria');
    const formData = new FormData(form);

    fetch('categorias/editar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === "ok") {
            Swal.fire({
                icon: 'success',
                title: 'Categoría actualizada correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                cargarContenido('categorias/index.php');
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'No se pudo actualizar la categoría',
            });
        }
    })
    .catch(err => {
        console.error('Error en la petición fetch:', err);
        Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: 'Algo salió mal. Inténtalo más tarde.',
        });
    });
}
</script>
