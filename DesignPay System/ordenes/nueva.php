<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '1') exit("Acceso denegado");

$id_vendedor = $_SESSION['id_usuario'];
$verificar = $conn->query("SELECT * FROM ordenes_en_tiempo_real 
                           WHERE id_vendedor = $id_vendedor 
                           AND estado IN ('activa', 'pausada')");
if ($verificar->num_rows > 0) {
    echo '<div class="alert alert-warning" role="alert">
            ⚠️ Ya tienes una orden en proceso. Solo puedes tener una a la vez.
          </div>
          <button class="btn btn-primary" onclick="cargarContenido(\'ordenes/cronometro.php\')">
            🔁 Volver a la Orden
          </button>';
    exit();
}
?>

<h2 class="mb-4">📄 Nueva Orden en Tiempo Real</h2>

<form id="formOrdenTiempoReal" class="needs-validation" novalidate>
    <div class="mb-3">
        <label class="form-label">Tipo de Impresión:</label>
        <select name="tipo_impresion" class="form-select" required>
            <option value="">Seleccione una opción</option>
            <option value="laser">Impresión Láser (7 min gratis)</option>
            <option value="digital">Impresión Digital (15 min gratis)</option>
        </select>
    </div>

    <div class="mb-4">
        <label class="form-label">Canal de Atención:</label>
        <select name="canal" class="form-select" required>
            <option value="">Seleccione un canal</option>
            <option value="presencial">Presencial</option>
            <option value="en_linea">En Línea (WhatsApp, Facebook, etc.)</option>
        </select>
    </div>

    <button type="submit" class="btn btn-success w-100">✅ Iniciar Orden</button>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById("formOrdenTiempoReal").addEventListener("submit", async function(e) {
    e.preventDefault();

    const tipo = this.tipo_impresion.value;
    const canal = this.canal.value;

    if (!tipo || !canal) {
        Swal.fire({
            icon: "warning",
            title: "Campos incompletos",
            text: "Por favor selecciona el tipo de impresión y canal de atención."
        });
        return;
    }

    const formData = new FormData(this);

    try {
        const response = await fetch("ordenes/iniciar.php", {
            method: "POST",
            body: formData
        });

			const resultado = (await response.text()).trim().toLowerCase();

			if (resultado === "ok") {
            Swal.fire({
                icon: "success",
                title: "Orden iniciada",
                text: "Redirigiendo al cronómetro...",
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                cargarContenido("ordenes/cronometro.php");
            });
        } else {
            Swal.fire({
                icon: "warning",
                title: "⚠️ Atención",
                text: resultado
            });
        }
    } catch (error) {
        Swal.fire({
            icon: "error",
            title: "Error de red",
            text: "No se pudo iniciar la orden."
        });
    }
});
</script>