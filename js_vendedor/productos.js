function cargarDatosProducto() {
    let selectProducto = document.getElementById("id_producto");
    if (!selectProducto){
        console.log("no existe selectProducto");
        return;
    }
    let idProducto = selectProducto.value;
    console.log("Producto seleccionado:", idProducto);
    let codigo = selectProducto.options[selectProducto.selectedIndex].getAttribute("data-codigo");
    document.getElementById("codigo_producto").value = codigo || "";

    fetch("obtener_colores_actualizar.php?id_producto=" + idProducto)
        .then(res => res.json())
        .then(data => {
            console.log("colores recibidos:", data);
            let selectColor = document.getElementById("select_color");

            selectColor.innerHTML = "<option value=''>Selecciona un color</option>";

            data.forEach(item => {
                selectColor.innerHTML += `
                    <option value="${item.color}">
                        ${item.color}
                    </option>
                `;
            });
        })
        .catch(err => {
            console.log("Error cargando colores:", err);
        });
}