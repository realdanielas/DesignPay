<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] !== '2') {
    exit("Acceso denegado");
}
?>

<div class="container mt-4">
    <h2 class="mb-4">➕ Agregar Nuevo Usuario</h2>
    <form id="formAgregarUsuario">
        <div class="mb-3">
            <label for="usuario" class="form-label">Nombre de usuario</label>
            <input type="text" class="form-control" id="usuario" name="usuario" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="tipo_usuario" class="form-label">Tipo de usuario</label>
            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                <option value="1">Vendedor</option>
                <option value="2">Administrador</option>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Guardar Usuario</button>
        <button type="button" class="btn btn-secondary" onclick="cargarContenido('usuarios/index.php')">Cancelar</button>
    </form>
</div>

<script>
document.getElementById('formAgregarUsuario').addEventListener('submit', function(e) {
    e.preventDefault();
    const datos = new FormData(this);

    fetch('usuarios/agregar_procesar.php', {
        method: 'POST',
        body: datos
    })
    .then(r => r.text())
    .then(res => {
        if (res.trim() === 'ok') {
            Swal.fire({
                icon: 'success',
                title: 'Usuario creado correctamente',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                cargarContenido('usuarios/index.php');
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: res
            });
        }
    });
});
</script>
