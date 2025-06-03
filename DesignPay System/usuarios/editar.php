<?php
include("../verificar_admin.php");
include("../conexion.php");

// Manejo POST: guardar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: text/plain");

    $id = $_POST['id'];
    $usuario = trim($_POST['usuario']);
    $password = trim($_POST['password']);
    $tipo = $_POST['tipo_usuario'];

    // Si se proporciona una nueva contraseña, actualizarla con hash
    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, password = ?, tipo_usuario = ? WHERE id = ?");
        $stmt->bind_param("ssii", $usuario, $passwordHash, $tipo, $id);
    } else {
        // No se actualiza la contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, tipo_usuario = ? WHERE id = ?");
        $stmt->bind_param("sii", $usuario, $tipo, $id);
    }

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }
    exit();
}

// Manejo GET: cargar datos del usuario
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$usuario = $res->fetch_assoc();
?>

<div class="container mt-4">
    <h3 class="mb-4">Editar Usuario</h3>
    <form id="form-editar-usuario" onsubmit="actualizarUsuario(event)">
        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

        <div class="mb-3">
            <label for="usuario" class="form-label">Nombre de usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nueva contraseña <small class="text-muted">(opcional)</small></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Dejar en blanco para no cambiar">
        </div>

        <div class="mb-3">
            <label for="tipo_usuario" class="form-label">Tipo de usuario</label>
            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                <option value="1" <?= $usuario['tipo_usuario'] == 1 ? 'selected' : '' ?>>Vendedor</option>
                <option value="2" <?= $usuario['tipo_usuario'] == 2 ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <button type="button" class="btn btn-secondary ms-2" onclick="cargarContenido('usuarios/index.php')">Cancelar</button>
    </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script para actualizar usuario -->
<script>
function actualizarUsuario(event) {
    event.preventDefault();
    const form = document.getElementById('form-editar-usuario');
    const formData = new FormData(form);

    fetch('usuarios/editar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === "ok") {
            Swal.fire({
                icon: 'success',
                title: 'Usuario actualizado correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                cargarContenido('usuarios/index.php');
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'No se pudo actualizar el usuario.',
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
