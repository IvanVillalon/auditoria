console.log("devoluciones.js cargado");
let idVentaSeleccionada = null;

window.seleccionarFactura = function(numero, idVenta) {

    idVentaSeleccionada = idVenta;

    mostrarTablasNotaCredito();
    resetearFilasDetalle();
    mostrarSoloFactura(numero);
}
function devolucioncompleta(){
    procesarDevolucion("total");
}

function devolucionparcial(){
    procesarDevolucion("parcial");
}

function procesarDevolucion(tipoForzado = null) {

    let productos = document.querySelectorAll("#tabla_detalle tbody tr[data-id]");
    let comentarioInput = document.getElementById("comentario_nota");
    let comentario = comentarioInput ? comentarioInput.value : "";

    let botones = document.querySelectorAll("button");

    // 🔒 bloquear botones
    botones.forEach(btn => btn.disabled = true);

    if (comentario.trim() === "") {
        Swal.fire("Error", "El comentario es obligatorio", "warning");
        botones.forEach(btn => btn.disabled = false);
        return;
    }

    let datos = [];
    let error = false;

    Swal.fire({
        title: "Procesando...",
        text: "Registrando nota de crédito",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    productos.forEach(fila => {

        if (!fila.offsetParent) return;

        let checkbox = fila.querySelector(".check_producto");
        if (!checkbox || !checkbox.checked) return;

        let id_producto = fila.dataset.id;
        let color = fila.dataset.color;

        let disponible = parseInt(fila.cells[4].textContent.trim());

        let inputCantidad = fila.querySelector(".cantidad_devolver");
        let cantidadDevolver = parseInt(inputCantidad.value) || 0;

        let estadoSelect = fila.querySelector(".estado_producto");
        let estadoProducto = estadoSelect ? estadoSelect.value : "";

        if (!estadoProducto) {
            Swal.fire("Error", "Debes seleccionar el estado del producto", "warning");
            error = true;
            return;
        }

        if (tipoForzado === "total") {
            cantidadDevolver = disponible;
        }

        if (cantidadDevolver < 1 || cantidadDevolver > disponible) {
            Swal.fire("Error", "Cantidad inválida", "warning");
            error = true;
            return;
        }

        let precio = parseFloat(
            fila.cells[5].textContent
                .replace("$", "")
                .replace(/\./g, "")
                .replace(",", ".")
        );

        let subtotal = precio * cantidadDevolver;

        datos.push({
            id_producto : id_producto,
            color,
            cantidad: cantidadDevolver,
            precio,
            subtotal,
            estado: estadoProducto
        });
    });

    if (error) {
        botones.forEach(btn => btn.disabled = false);
        return;
    }

    if (datos.length === 0) {
        Swal.fire("Error", "Selecciona al menos un producto", "warning");
        botones.forEach(btn => btn.disabled = false);
        return;
    }

    if (!idVentaSeleccionada) {
        Swal.fire("Error", "No se ha seleccionado una venta", "error");
        botones.forEach(btn => btn.disabled = false);
        return;
    }
    fetch("api/procesar_nota_credito.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify({
        tipo: tipoForzado,
        productos: datos,
        id_venta: idVentaSeleccionada,
        comentario: comentario
    })
})
.then(async (r) => {
    const text = await r.text();
    console.log("RESPUESTA CRUDA:", text);

    try {
        return JSON.parse(text);
    } catch (e) {
        console.error("JSON roto:", text);
        throw new Error("Respuesta inválida del servidor");
    }
})
.then(res => {

    console.log("RESPUESTA PARSEADA:", res);

    // 🚨 SOLO ERROR REAL DEL BACKEND
    if (res.status !== "ok") {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: res.mensaje || "Error desconocido"
        });
        return;
    }

    // ✔ ÉXITO REAL
    Swal.fire({
        icon: "success",
        title: "✔ Nota registrada",
        text: res.mensaje
    }).then(() => {

        if (res.pdf) {
            window.open(res.pdf, "_blank");
        }

        location.reload();
    });

})
.catch(err => {

    console.error("ERROR REAL:", err);

    Swal.fire({
        icon: "error",
        title: "Error de servidor",
        text: err.message || "No se pudo completar la operación"
    });

})
.finally(() => {
    botones.forEach(b => b.disabled = false);
});

}
function mostrarTablasNotaCredito() {

    const tablaDetalle = document.getElementById("tabla_detalle");
    const tablaAcciones = document.getElementById("tabla_acciones_nota_credito");

    if (tablaDetalle) {
        tablaDetalle.style.display = "table";
    }

    if (tablaAcciones) {
        tablaAcciones.style.display = "table";
    }
}
function resetearFilasDetalle() {

    const filas = document.querySelectorAll("#tabla_detalle tbody tr");

    filas.forEach(fila => {

        fila.style.display = "none";

        const check = fila.querySelector(".check_producto");
        const select = fila.querySelector(".estado_producto");

        if (check) {
            check.checked = false;
        }

        if (select) {
            select.disabled = true;
            select.value = "";
        }
    });
}
function mostrarSoloFactura(numero) {

    const filas = document.querySelectorAll(
        `#tabla_detalle tbody tr[data-factura="${numero}"]`
    );

    console.log("Filas encontradas:", filas.length);

    filas.forEach(fila => {

        fila.style.display = "table-row";
    });
}