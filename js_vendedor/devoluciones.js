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
window.llenarTablaAjustesValores = function() {
    const tbody = document.getElementById("cuerpo_ajuste_valores");
    tbody.innerHTML = "";

    const filas = document.querySelectorAll("#tabla_detalles tbody tr[data-id]");

    filas.forEach(fila => {
        if (fila.style.display === "none") return;

        const producto    = fila.cells[0].textContent.trim();
        const color       = fila.cells[1].textContent.trim();
        const precio      = fila.cells[5].textContent.trim();
        const idProducto  = fila.dataset.id;

        const precioNumero = precio
            .replace("$", "")
            .replace(/\./g, "")
            .replace(",", ".")
            .trim();

        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${producto}</td>
            <td>${color}</td>
            <td>${precio}</td>
            <td>
                <input 
                    type="number" 
                    class="form-control nuevo_precio" 
                    data-id="${idProducto}"
                    min="0" 
                    value="${precioNumero}"
                    style="width:120px; margin:auto;">
            </td>
        `;
        tbody.appendChild(tr);
    });
}
window.cambiarTipoNota = function(selectElement){
    const tipo = selectElement.value;
    const contenedorAcciones = document.getElementById("tabla_acciones_nota_credito");
    const ajusteValorDiv = document.querySelector(".ajuste_valor");

    if (ajusteValorDiv) ajusteValorDiv.style.display = "none";
    contenedorAcciones.style.display = "flex";

    contenedorAcciones.style.display = "flex";

    if (tipo === "ajuste_precio" || tipo === "ajuste_descuento") {
        ajusteValorDiv.style.display = "flex";
        window.llenarTablaAjustesValores();
    }
}
function procesarDevolucion(tipoForzado = null) {

    let productos      = document.querySelectorAll("#tabla_detalles tbody tr[data-id]");
    let comentarioInput = document.getElementById("comentario_nota");
    let comentario     = comentarioInput ? comentarioInput.value : "";
    let botones        = document.querySelectorAll("button");

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

        // ✅ Declarar todas las variables primero
        let id_producto  = fila.dataset.id;
        let color        = fila.dataset.color;
        let disponible   = parseInt(fila.cells[4].textContent.trim());

        let inputCantidad    = fila.querySelector(".cantidad_devolver");
        let cantidadDevolver = parseInt(inputCantidad.value) || 0;

        let precio = parseFloat(
            fila.cells[5].textContent
                .replace("$", "")
                .replace(/\./g, "")
                .replace(",", ".")
                .trim()
        );

        let tipoNotaSelect = fila.querySelector(".tipo_nota_credito");
        let tipoNota       = tipoNotaSelect ? tipoNotaSelect.value : "devolucion";

        // ✅ Aplicar cantidad total si es forzado
        if (tipoForzado === "total") {
            cantidadDevolver = disponible;
        }

        if (cantidadDevolver < 1 || cantidadDevolver > disponible) {
            Swal.fire("Error", "Cantidad inválida", "warning");
            error = true;
            return;
        }

        let subtotal = precio * cantidadDevolver;

        // ✅ Validar estado solo en devolución
        let estadoSelect   = fila.querySelector(".estado_producto");
        let estadoProducto = estadoSelect ? estadoSelect.value : "";

        if (tipoNota === "devolucion" && !estadoProducto) {
            Swal.fire("Error", "Debes seleccionar el estado del producto", "warning");
            error = true;
            return;
        }

        // ✅ Leer nuevo precio solo en ajuste_precio
        let nuevoPrecio = null;
        if (tipoNota === "ajuste_precio") {
            let inputPrecio = document.querySelector(`.nuevo_precio[data-id="${id_producto}"]`);
            nuevoPrecio = inputPrecio ? parseFloat(inputPrecio.value) : precio;
        }

        // ✅ Un solo datos.push con todos los campos
        datos.push({
            id_producto,
            id_venta:    idVentaSeleccionada,
            color,
            cantidad:    cantidadDevolver,
            precio,
            nuevo_precio: nuevoPrecio,
            subtotal,
            tipo_nota:   tipoNota,
            estado:      estadoProducto
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
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            tipo:         tipoForzado,
            productos:    datos,
            id_venta:     idVentaSeleccionada,
            comentario:   comentario
        })
    })
    .then(async (r) => {
        const text = await r.text();
        console.log("RESPUESTA CRUDA:", text);
        const jsonMatch = text.match(/\{.*\}/s);
        if (!jsonMatch) throw new Error("Respuesta inválida del servidor");
        return JSON.parse(jsonMatch[0]);
    })
    .then(res => {
        if (res.status !== "ok") {
            Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "Error desconocido" });
            return;
        }
        Swal.fire({ icon: "success", title: "✔ Nota registrada", text: res.mensaje })
            .then(() => {
                if (res.pdf) window.open(res.pdf, "_blank");
                location.reload();
            });
    })
    .catch(err => {
        console.error("ERROR:", err);
        Swal.fire({ icon: "error", title: "Error de servidor", text: err.message });
    })
    .finally(() => {
        botones.forEach(b => b.disabled = false);
    });
}
function mostrarTablasNotaCredito() {

    const tablaDetalle = document.getElementById("tabla_detalles");
    const tablaAcciones = document.getElementById("tabla_acciones_nota_credito");
    const contenedor = document.getElementById("container-detalles");
    if (tablaDetalle) {
        tablaDetalle.style.display = "table";
    }

    if (tablaAcciones) {
        tablaAcciones.style.setProperty('display', 'flex', 'important');
    }
    if (contenedor){
        contenedor.style.display = "flex";
    }
}
function resetearFilasDetalle() {

    const filas = document.querySelectorAll("#tabla_detalles tbody tr");

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
        `#tabla_detalles tbody tr[data-factura="${numero}"]`
    );

    console.log("Filas encontradas:", filas.length);

    filas.forEach(fila => {

        fila.style.display = "table-row";
    });
}