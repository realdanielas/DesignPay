<?php
include("../conexion.php");
session_start();

$id_vendedor = $_SESSION['id_usuario'];
$orden = $conn->query("SELECT * FROM ordenes_en_tiempo_real 
                       WHERE id_vendedor = $id_vendedor 
                       AND estado IN ('activa', 'pausada')")->fetch_assoc();

if (!$orden) {
    echo "<div class='alert alert-info'>No hay una orden activa o pausada para mostrar.</div>";
    exit();
}

$min_gratis = $orden['tipo_impresion'] === 'laser' ? 7 : 15;
$estado = $orden['estado'];
$inicio = strtotime($orden['inicio']);
$ahora = time();
$segundos_totales = ($estado === 'activa')
    ? ($ahora - $inicio) + intval($orden['segundos_acumulados'])
    : intval($orden['segundos_acumulados']);
?>

<div class="card shadow p-4">
  <h3 class="mb-3">⏱ Orden en Proceso</h3>

  <ul class="list-group mb-3">
    <li class="list-group-item"><strong>Tipo de impresión:</strong> <?= ucfirst($orden['tipo_impresion']) ?></li>
    <li class="list-group-item"><strong>Canal:</strong> <?= ucfirst($orden['canal']) ?></li>
    <li class="list-group-item"><strong>Minutos gratis:</strong> <?= $min_gratis ?></li>
  </ul>

  <div id="cronometro" class="fs-4 fw-bold text-primary mb-2"></div>
  <div id="cronometro-pausado" class="fs-4 fw-bold text-warning mb-2" style="display: none;"></div>
  <p id="cobro" class="text-danger fw-semibold"></p>

  <div class="d-flex flex-wrap gap-2 mt-3">
    <?php
    $mostrarReiniciar = false;
    $minutos_transcurridos = floor($segundos_totales / 60);
    if ($estado === 'activa') {
        echo '<button onclick="pausarOrden()" class="btn btn-warning">⏸ Pausar</button>';
        if ($minutos_transcurridos <= $min_gratis) {
            $mostrarReiniciar = true;
        }
    } elseif ($estado === 'pausada') {
        echo '<button onclick="reanudarOrden()" class="btn btn-success">▶️ Reanudar</button>';
        $mostrarReiniciar = true;
    }

    if ($mostrarReiniciar) {
        echo '<button onclick="reiniciarOrden()" class="btn btn-secondary">🔄 Reiniciar desde Cero</button>';
    }
    ?>
    <button onclick="finalizarOrden()" class="btn btn-primary">✅ Finalizar Orden</button>
  </div>
</div>

<!-- Datos ocultos -->
<input type="hidden" id="orden-inicio" value="<?= date("c", strtotime($orden['inicio'])) ?>">
<input type="hidden" id="orden-minutos-gratis" value="<?= $min_gratis ?>">
<input type="hidden" id="orden-segundos-acumulados" value="<?= $orden['segundos_acumulados'] ?>">
<input type="hidden" id="orden-estado" value="<?= $orden['estado'] ?>">

<!-- SWEETALERT2 + JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    const estado = document.getElementById('orden-estado').value;
    const segundosAcumulados = parseInt(document.getElementById('orden-segundos-acumulados').value);
    const minutosGratis = parseInt(document.getElementById('orden-minutos-gratis').value);
    const inicio = new Date(document.getElementById('orden-inicio').value);
    const divActivo = document.getElementById('cronometro');
    const divPausado = document.getElementById('cronometro-pausado');
    const cobro = document.getElementById('cobro');

    if (estado === 'activa') {
        divActivo.style.display = 'block';
        divPausado.style.display = 'none';

        let inicioMs = inicio.getTime();
        setInterval(() => {
            const ahora = new Date().getTime();
            const diff = Math.floor((ahora - inicioMs) / 1000) + segundosAcumulados;

            const h = String(Math.floor(diff / 3600)).padStart(2, '0');
            const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
            const s = String(diff % 60).padStart(2, '0');

            divActivo.innerText = `Tiempo: ${h}:${m}:${s}`;

            const minutosExactos = diff / 60;
            if (minutosExactos > minutosGratis) {
                const excedente = Math.floor(minutosExactos - minutosGratis);
                const monto = (excedente * 0.15).toFixed(2);
                cobro.innerText = `➕ Excedente: ${excedente} min ($${monto} USD)`;
            } else {
                cobro.innerText = '🕐 Aún en tiempo gratuito';
            }
        }, 1000);
    } else {
        divActivo.style.display = 'none';
        divPausado.style.display = 'block';

        const h = String(Math.floor(segundosAcumulados / 3600)).padStart(2, '0');
        const m = String(Math.floor((segundosAcumulados % 3600) / 60)).padStart(2, '0');
        const s = String(segundosAcumulados % 60).padStart(2, '0');

        divPausado.innerText = `⏸ Tiempo pausado: ${h}:${m}:${s}`;

        const minutosExactos = segundosAcumulados / 60;
        if (minutosExactos > minutosGratis) {
            const excedente = Math.floor(minutosExactos - minutosGratis);
            const monto = (excedente * 0.15).toFixed(2);
            cobro.innerText = `➕ Excedente: ${excedente} min ($${monto} USD)`;
        } else {
            cobro.innerText = '🕐 Aún en tiempo gratuito';
        }
    }

    window.pausarOrden = () => {
        fetch('ordenes/actualizar_estado.php?accion=pausar')
            .then(r => r.text())
            .then(res => {
                if (res.trim() === 'ok') {
                    cargarContenido('ordenes/cronometro.php');
                } else {
                    Swal.fire("Error", "No se pudo pausar la orden.", "error");
                }
            });
    };

    window.reanudarOrden = () => {
        fetch('ordenes/actualizar_estado.php?accion=reanudar')
            .then(r => r.text())
            .then(res => {
                if (res.trim() === 'ok') {
                    cargarContenido('ordenes/cronometro.php');
                } else {
                    Swal.fire("Error", "No se pudo reanudar la orden.", "error");
                }
            });
    };

    window.reiniciarOrden = () => {
        Swal.fire({
            title: '¿Reiniciar orden?',
            text: "Esto eliminará la orden actual.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, reiniciar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('ordenes/reiniciar.php')
                    .then(r => r.text())
                    .then(res => {
                        if (res.trim() === 'ok') {
                            Swal.fire("Orden reiniciada", "", "success");
                            cargarContenido('ordenes/nueva.php');
                        } else {
                            Swal.fire("Error", "No se pudo reiniciar la orden.", "error");
                        }
                    });
            }
        });
    };

    window.finalizarOrden = () => {
        Swal.fire({
            title: '¿Finalizar orden?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('ordenes/finalizar.php')
                    .then(r => r.text())
                    .then(res => {
                        if (res.trim() === 'ok') {
                            Swal.fire({
                                title: '✅ Orden finalizada',
                                text: "¿Deseas generar un pedido ahora?",
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Ir a pedidos',
                                cancelButtonText: 'Volver a órdenes'
                            }).then(op => {
                                if (op.isConfirmed) {
                                    cargarContenido('pedidos/index.php');
                                } else {
                                    cargarContenido('ordenes/nueva.php');
                                }
                            });
                        } else {
                            Swal.fire("Error", "No se pudo finalizar la orden.", "error");
                        }
                    });
            }
        });
    };
})();
</script>
