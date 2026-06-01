window.cargarColoresExistentes = function () {

    const select = document.getElementById("producto_actualizar");

    if (!(select instanceof HTMLSelectElement)) {
        console.warn("producto_actualizar no está disponible en el DOM");
        return;
    }

    console.log("Producto seleccionado:", select.value);
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
