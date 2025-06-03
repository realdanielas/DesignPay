// ✅ Carga contenido dinámico y ejecuta los scripts embebidos
function cargarContenido(url) {
    const contenedor = document.getElementById('contenido-dinamico');
    fetch(url)
        .then(response => response.text())
        .then(html => {
            contenedor.innerHTML = html;

            // ⚡ Ejecutar scripts embebidos
            const scripts = contenedor.querySelectorAll("script");
            scripts.forEach(oldScript => {
                const newScript = document.createElement("script");
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                document.body.appendChild(newScript);
                oldScript.remove();
            });
        })
        .catch(error => {
            contenedor.innerHTML = '<p>Error al cargar el contenido.</p>';
            console.error(error);
        });
}

function guardarCategoria(event) {
    event.preventDefault();
    const form = event.target;
    const datos = new FormData(form);

    fetch('categorias/agregar.php', {
        method: 'POST',
        body: datos
    })
    .then(response => response.text())
    .then(() => {
        cargarContenido('categorias/index.php');
    })
    .catch(error => {
        alert('Error al guardar la categoría');
        console.error(error);
    });
}

function actualizarCategoria(event, id) {
    event.preventDefault();
    const form = event.target;
    const datos = new FormData(form);

    fetch('categorias/editar.php?id=' + id, {
        method: 'POST',
        body: datos
    })
    .then(response => response.text())
    .then(respuesta => {
        if (respuesta.trim() === 'ok') {
            cargarContenido('categorias/index.php');
        } else {
            alert('Error al actualizar la categoría');
        }
    })
    .catch(error => {
        alert('Error de conexión');
        console.error(error);
    });
}

function guardarProducto(event) {
    event.preventDefault();
    const form = event.target;
    const datos = new FormData(form);

    fetch('productos/agregar.php', {
        method: 'POST',
        body: datos
    })
    .then(response => response.text())
    .then(res => {
        if (res.trim() === 'ok') {
            cargarContenido('productos/index.php');
        } else {
            alert('Error al guardar producto.');
        }
    });
}

function actualizarProducto(event, id) {
    event.preventDefault();
    const form = event.target;
    const datos = new FormData(form);

    fetch('productos/editar.php?id=' + id, {
        method: 'POST',
        body: datos
    })
    .then(response => response.text())
    .then(res => {
        if (res.trim() === 'ok') {
            cargarContenido('productos/index.php');
        } else {
            alert('Error al actualizar producto.');
        }
    });
}

function crearPedido(event) {
    event.preventDefault();
    const form = document.getElementById('formPedido');
    const datos = new FormData(form);

    fetch('pedidos/agregar.php', {
        method: 'POST',
        body: datos
    })
    .then(r => r.text())
    .then(res => {
        if (res.trim() === "ok") {
            cargarContenido('pedidos/index.php');
        } else if (res.trim() === "stock_error") {
            alert("Error: No hay suficiente stock para uno o más productos.");
        } else if (res.trim() === "sin_items") {
            alert("Debes seleccionar al menos un producto.");
        } else {
            alert("Ocurrió un error al registrar el pedido.");
        }
    });
}

function cargarDetallePedido(idPedido) {
    fetch('pedidos/detalle.php?id=' + idPedido)
        .then(r => r.text())
        .then(html => {
            document.getElementById('detalle-pedido-modal').innerHTML = html;
            document.getElementById('modal-overlay').style.display = 'flex';
        });
}

function cerrarModal() {
    document.getElementById('modal-overlay').style.display = 'none';
    document.getElementById('detalle-pedido-modal').innerHTML = '';
}

function filtrarCanal(canal) {
    cargarContenido('pedidos/admin.php?canal=' + canal);
}

function actualizarEstado(id, accion) {
    if (!confirm(`¿Confirmar marcar como ${accion === 'cobrar' ? 'COBRADO' : 'ENTREGADO'}?`)) return;

    fetch(`pedidos/actualizar_estado.php?id=${id}&accion=${accion}`)
        .then(r => r.text())
        .then(res => {
            if (res.trim() === "ok") {
                cargarContenido('pedidos/admin.php');
            } else {
                alert("Error al actualizar el estado.");
            }
        });
}

function aplicarFiltros() {
    const form = document.getElementById('filtrosPedidos');
    const params = new URLSearchParams(new FormData(form)).toString();
    cargarContenido('pedidos/admin.php?' + params);
}

function descargarPDF(pedidoId) {
    const element = document.getElementById('detalle-impresion');

    const botones = element.querySelectorAll('.btn-no-pdf');
    botones.forEach(btn => btn.style.display = 'none');

    const opt = {
        margin: [0.8, 0.8, 0.8, 0.8],
        filename: `pedido_${pedidoId}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        botones.forEach(btn => btn.style.display = 'inline-block');
    });
}

function eliminarUsuario(id) {
    if (!confirm("¿Estás seguro de eliminar este usuario?")) return;

    fetch('usuarios/eliminar.php?id=' + id)
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'ok') {
                alert("✅ Usuario eliminado");
                cargarContenido('usuarios/index.php');
            } else {
                alert("❌ " + res);
            }
        });
}
