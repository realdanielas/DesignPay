let cronometroInterval = null;

// ✅ Detiene el cronómetro visual (cuando se pausa)
function detenerCronometro() {
    if (cronometroInterval) {
        clearInterval(cronometroInterval);
        cronometroInterval = null;
    }
}

// ✅ Inicia el cronómetro visual (cuando la orden está activa)
function iniciarCronometro(fechaInicio, minutosGratis, segundosAcumulados = 0) {
    console.log("🕒 Recibido:", fechaInicio);

    detenerCronometro();

    const inicio = new Date(fechaInicio);
    if (isNaN(inicio.getTime())) {
        console.error("❌ Fecha inválida:", fechaInicio);
        document.getElementById('cronometro').innerText = "Error: Fecha inválida";
        return;
    }

    const inicioMs = inicio.getTime();

    // 🔁 Asegura que se vea solo el cronómetro activo
    const divActivo = document.getElementById('cronometro');
    const divPausado = document.getElementById('cronometro-pausado');
    if (divActivo) divActivo.style.display = 'block';
    if (divPausado) divPausado.style.display = 'none';

    cronometroInterval = setInterval(() => {
        const ahora = new Date().getTime();
        const diff = Math.floor((ahora - inicioMs) / 1000) + parseInt(segundosAcumulados);

        if (diff < 0) {
            console.warn("⚠️ Tiempo negativo detectado. Se detendrá.");
            detenerCronometro();
            document.getElementById('cronometro').innerText = "Tiempo inválido";
            return;
        }

        const horas = Math.floor(diff / 3600);
        const minutos = Math.floor((diff % 3600) / 60);
        const segundos = diff % 60;

        const hStr = String(horas).padStart(2, '0');
        const mStr = String(minutos).padStart(2, '0');
        const sStr = String(segundos).padStart(2, '0');

        const cronometro = document.getElementById('cronometro');
        const cobro = document.getElementById('cobro');

        if (cronometro) {
            cronometro.innerText = `Tiempo: ${hStr}:${mStr}:${sStr}`;
        }

        if (minutos > minutosGratis) {
            const excedente = minutos - minutosGratis;
            const monto = (excedente * 0.15).toFixed(2);
            if (cobro) cobro.innerText = `➕ Excedente: ${excedente} min ($${monto} USD)`;
        } else {
            if (cobro) cobro.innerText = '🕐 Aún en tiempo gratuito';
        }
    }, 1000);
}

// ✅ Pausa la orden y detiene el cronómetro visual
function pausarOrden() {
    detenerCronometro();

    fetch('ordenes/actualizar_estado.php?accion=pausar')
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'ok') {
                cargarContenido('ordenes/cronometro.php');
            } else {
                alert("No se pudo pausar la orden.");
            }
        });
}

// ✅ Reanuda la orden y reactiva el cronómetro visual
function reanudarOrden() {
    fetch('ordenes/actualizar_estado.php?accion=reanudar')
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'ok') {
                cargarContenido('ordenes/cronometro.php');
            } else {
                alert("No se pudo reanudar la orden.");
            }
        });
}

// ✅ Reinicia completamente la orden
function reiniciarOrden() {
    if (!confirm("⚠️ Esto eliminará la orden actual.\n¿Estás seguro de reiniciar desde cero?")) return;

    fetch('ordenes/reiniciar.php')
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'ok') {
                cargarContenido('ordenes/nueva.php');
            } else {
                alert("No se pudo reiniciar la orden.");
            }
        });
}

// ✅ Finaliza la orden y ofrece redirección
function finalizarOrden() {
    if (!confirm("¿Deseas finalizar esta orden?")) return;

    detenerCronometro();
    fetch('ordenes/finalizar.php')
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'ok') {
                const irPedido = confirm("✅ Orden finalizada correctamente.\n¿Deseas generar un pedido ahora?");
                if (irPedido) {
                    cargarContenido('pedidos/index.php');
                } else {
                    cargarContenido('ordenes/nueva.php');
                }
            } else {
                alert("Error al finalizar orden.");
            }
        });
}
