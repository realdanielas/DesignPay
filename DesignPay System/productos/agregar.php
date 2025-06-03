<?php
include("../verificar_admin.php");
include("../conexion.php");

$categorias = $conn->query("SELECT * FROM categorias");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("INSERT INTO productos (nombre, material, precio, cantidad, descripcion, stock, id_categoria) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisii", $_POST['nombre'], $_POST['material'], $_POST['precio'], $_POST['cantidad'], $_POST['descripcion'], $_POST['stock'], $_POST['id_categoria']);
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "Error: " . $stmt->error;
    }
    exit();
}
?>

<h2 class="mb-4">➕ Agregar Producto</h2>

<form id="formProducto" class="row g-3" onsubmit="guardarProducto(event)">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Material</label>
        <input type="text" name="material" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio</label>
        <input type="number" name="precio" step="0.01" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Cantidad</label>
        <input type="number" name="cantidad" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Stock</label>
        <input type="number" name="stock" class="form-control" required>
    </div>

    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2"></textarea>
    </div>

    <div class="col-md-6">
        <label class="form-label">Categoría</label>
        <select name="id_categoria" class="form-select" required>
            <option value="">Seleccione Categoría</option>
            <?php while ($c = $categorias->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Guardar Producto</button>
        <button type="button" class="btn btn-secondary" onclick="cargarContenido('productos/index.php')">Cancelar</button>
    </div>
</form>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function guardarProducto(e) {
    e.preventDefault();
    const form = document.getElementById('formProducto');
    const formData = new FormData(form);

    fetch('productos/agregar.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(res => {
        if (res.trim() === 'ok') {
            Swal.fire({
                icon: 'success',
                title: 'Producto agregado',
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
        Swal.fire('Error', 'No se pudo guardar el producto.', 'error');
    });
}
</script>
