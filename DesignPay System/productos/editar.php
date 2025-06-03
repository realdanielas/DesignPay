<?php
include("../verificar_admin.php");
include("../conexion.php");

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$p = $res->fetch_assoc();

$categorias = $conn->query("SELECT * FROM categorias");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, material = ?, precio = ?, cantidad = ?, descripcion = ?, stock = ?, id_categoria = ? WHERE id = ?");
    $stmt->bind_param("ssdisiii", $_POST['nombre'], $_POST['material'], $_POST['precio'], $_POST['cantidad'], $_POST['descripcion'], $_POST['stock'], $_POST['id_categoria'], $id);
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error: " . $stmt->error;
    }
    exit();
}
?>

<h2 class="mb-4">✏️ Editar Producto</h2>

<form id="formEditarProducto" class="row g-3" onsubmit="actualizarProducto(event, <?= $p['id'] ?>)">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($p['nombre']) ?>" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Material</label>
        <input type="text" name="material" class="form-control" value="<?= htmlspecialchars($p['material']) ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio</label>
        <input type="number" name="precio" step="0.01" class="form-control" value="<?= $p['precio'] ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Cantidad</label>
        <input type="number" name="cantidad" class="form-control" value="<?= $p['cantidad'] ?>" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" value="<?= $p['stock'] ?>" required>
    </div>

    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2"><?= htmlspecialchars($p['descripcion']) ?></textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Categoría</label>
        <select name="id_categoria" class="form-select" required>
            <?php while ($c = $categorias->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $p['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-success">Actualizar Producto</button>
        <button type="button" class="btn btn-secondary" onclick="cargarContenido('productos/index.php')">Cancelar</button>
    </div>
</form>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function actualizarProducto(e, id) {
    e.preventDefault();
    const form = document.getElementById('formEditarProducto');
    const formData = new FormData(form);

    fetch('productos/editar.php?id=' + id, {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === 'ok') {
            Swal.fire({
                icon: 'success',
                title: 'Producto actualizado',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                cargarContenido('productos/index.php');
            });
        } else {
            Swal.fire('Error', res, 'error');
        }
    })
    .catch(() => {
        Swal.fire('Error', 'No se pudo actualizar el producto.', 'error');
    });
}
</script>
