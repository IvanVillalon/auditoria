window.cargarColoresExistentes = function () {

    const select = document.getElementById("producto_actualizar");

    if (!(select instanceof HTMLSelectElement)) {
        console.warn("producto_actualizar no está disponible en el DOM");
        return;
    }

    console.log("Producto seleccionado:", select.value);
}
        let idVentaSeleccionada = null;
        
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

function seleccionarFactura(numero, idVenta) {
    console.log("Factura seleccionada:", numero, "ID Venta:", idVenta);
    idVentaSeleccionada = idVenta;

    document.getElementById("tabla_detalle").style.display = "table";
    document.getElementById("tabla_acciones_nota_credito").style.display = "table";
    

    let filas = document.querySelectorAll("#tabla_detalle tbody tr");

    filas.forEach(fila => {
        fila.style.display = "none";
        let check = fila.querySelector(".check_producto");
        let selectEstado = fila.querySelector(".estado_producto");
        if (check) check.checked = false;
        if (selectEstado){ 
            selectEstado.disabled = true;
            selectEstado.value = "";}
    });

    // 🔥 MOSTRAR SOLO LAS DE ESA FACTURA
    let detalles = document.querySelectorAll(`#tabla_detalle tbody tr[data-factura="${numero}"]`);

    console.log("Filas encontradas para factura:", detalles.length);

  detalles.forEach(fila => {
    fila.style.display = "table-row";

    let check = fila.querySelector(".check_producto");

    if (check) {
        check.onchange = function () {
            let select = this.closest("tr").querySelector(".estado_producto");

            if (select) {
                select.disabled = !this.checked;

                if (!this.checked) {
                    select.value = ""; // limpia el select
                }
            }
        };
    }
});
}
function mostrarInputColorNuevo() {
    let select = document.getElementById("select_color");
    let input = document.getElementById("input_nuevo_color");

    if(select.value === "agregar_nuevo_color") {
        input.style.display = "block";
        input.required = true;
    } else {
        input.style.display = "none";
        input.required = false;
        input.value = ""; // limpia el campo
    }
}
function mostrarInputCategoriaNueva(){
    let select = document.getElementById("sub_categoria");
    let input = document.getElementById("input_nueva_categoria");
    if(select.value === "agregar_nueva_categoria"){
        input.style.display = "block";
        input.required = true;
    } else{
        input.style.display = "none";
        input.required = false;
        input.value = "";
    }
}
function mostrarColores() {
     let categoria = document.getElementById("categoria").value;
    let contenedor = document.getElementById("contenedorcolores");
    let contenedorOllas = document.getElementById("contenedor_catg_ollas");

    let sub = document.getElementById("sub_categoria");
    let tamaño = document.getElementById("tamaño");

    if(categoria === "Tela_tapiceria" || categoria === "Bateria_cocina" || categoria == "Papeleria") {
        contenedor.style.display = "block";
    } else {
        contenedor.style.display = "none";
    }

    if (categoria === "Bateria_cocina"){
        contenedorOllas.style.display = "block";

        sub.required = true;
        tamaño.required = true;

    } else{
        contenedorOllas.style.display = "none";

        sub.required = false;
        tamaño.required = false;

        sub.value = "";
        tamaño.value = "";
    }
}

document.addEventListener("DOMContentLoaded", () => {

    const categoria = document.querySelector("select[name='categoria_producto']");
    const contenedorColores = document.getElementById("contenedor_colores");

    if (!categoria || !contenedorColores) return;

    function toggleColores() {
        const valor = categoria.value;

        if (valor === "telas" || valor === "ropa_cama") {
            contenedorColores.style.display = "block";
        } else {
            contenedorColores.style.display = "none";
        }
    }

    categoria.addEventListener("change", toggleColores);

    // 👇 importante: ejecuta al cargar
    toggleColores();
});

function filtrarTabla(){
    let input = document.getElementById("buscador");
    let filtro = input.value.toLowerCase();
    let tabla = document.getElementById("tabla_facturas");
    let filas = tabla.getElementsByTagName("tr");
    for (let i = 1; i < filas.length; i++){
        let textofila = filas[i].textContent.toLowerCase();
        if (textofila.includes(filtro)){
            filas[i].style.display = "";
        } else {
            filas[i].style.display = "none";
        }
    }
}
/*
function cargarColoresExistentes() {
    let producto = document.getElementById("producto_actualizar").value;
    console.log("Producto seleccionado:", producto);
}*/

function mostraraccion() {

    const tipoSelect = document.getElementById("tipo_registro");

    if (!tipoSelect) return; // 👈 clave

    let tipo = tipoSelect.value;

    let contenedorNuevo = document.getElementById("contenedor_nuevo");
    let contenedorActualizar = document.getElementById("contenedor_actualizar");

    if (!contenedorNuevo || !contenedorActualizar) return;

    document.getElementById("accion").value = tipo;

    if (tipo === "nuevo") {
        contenedorNuevo.style.display = "block";
        contenedorActualizar.style.display = "none";
    } else if (tipo === "actualizar") {
        contenedorNuevo.style.display = "none";
        contenedorActualizar.style.display = "block";
    }
}


document.addEventListener("DOMContentLoaded", function () {
    let select = document.getElementById("select_color_actualizar");
    if (!select) {
        console.log("No se encontró el select de colores para actualizar");
        return;
    }

    if (select) {
        select.addEventListener("change", function () {
            let input = document.getElementById("input_nuevo_color_actualizar");

            if (this.value === "nuevo") {
                input.style.display = "block";
                input.required = true;
            } else {
                input.style.display = "none";
                input.required = false;
                input.value = "";
            }
        });
    }
});


// Mostrar input nuevo color en actualizar stock
function toggleRequired() {

    const tipoSelect = document.getElementById("tipo_registro");
    if (!tipoSelect) return;

    let tipo = tipoSelect.value;

    let nuevo = document.getElementById("contenedor_nuevo");
    let actualizar = document.getElementById("contenedor_actualizar");

    if (!nuevo || !actualizar) return;

    let inputsNuevo = nuevo.querySelectorAll("[required]");
    let inputsActualizar = actualizar.querySelectorAll("[required]");

    if (tipo === "nuevo") {

        inputsNuevo.forEach(el => el.disabled = false);
        inputsActualizar.forEach(el => el.disabled = true);

    } else if (tipo === "actualizar") {

        inputsNuevo.forEach(el => el.disabled = true);
        inputsActualizar.forEach(el => el.disabled = false);
    }
}
document.addEventListener("DOMContentLoaded", function(){
    toggleRequired();

const productoActualizar = document.getElementById("producto_actualizar");

if (productoActualizar) {
    productoActualizar.addEventListener("change", function () {

        let idProducto = this.value;

        let contenedor = document.getElementById("contenedorcolores_actualizar");
        let selectColor = document.getElementById("select_color_actualizar");

        if (idProducto == "") {
            contenedor.style.display = "none";
            return;
        }

        fetch("obtener_colores_actualizar.php?id_producto=" + idProducto)
            .then(res => res.json())
            .then(data => {
                selectColor.innerHTML = "<option value=''>Seleccione un color</option>";

                data.forEach(color => {
                    selectColor.innerHTML += `<option value="${color.color}">${color.color}</option>`;
                });

                selectColor.innerHTML += `<option value="nuevo">+ Agregar nuevo color</option>`;

                contenedor.style.display = "block";
            });

    
    } );
} else {
    console.log("No se encontró el select de productos para actualizar");
}
});
/*
document.addEventListener("DOMContentLoaded", function() {
    let selectProducto = document.querySelector("select[name='buscarproducto']");

    if (!selectProducto){
        console.log("no se encontro el select de productos");
        return;
    }
function actualizarDatos() {

    let selected = selectProducto.options[selectProducto.selectedIndex];

    console.log("option:", selected);

    let precio = selected.getAttribute("data-precio");
    let color = selected.getAttribute("data-color");

    const colorOculto = document.getElementById("color_oculto");
    const precioOculto = document.getElementById("precio_oculto");

    if (colorOculto) {
        colorOculto.value = color || "";
    }

    if (precioOculto) {
        precioOculto.value = precio || "";
    }

    console.log("precio:", precio);
    console.log("color:", color);
}

    selectProducto.addEventListener("change", actualizarDatos);

    actualizarDatos();
});*/
document.addEventListener("change", function(e) {

    if (e.target.matches(".check_producto")) {

        let fila = e.target.closest("tr");

        let select = fila.querySelector(".estado_producto");

        if (!select) {
            console.log("❌ No se encontró select");
            return;
        }

        console.log("✔ Checkbox clickeado");

        select.disabled = !e.target.checked;

    }

});



    
console.log("Script de checkbox cargado");
// Ejecutar al cargar la página Inicialización
window.addEventListener('DOMContentLoaded', mostraraccion);
