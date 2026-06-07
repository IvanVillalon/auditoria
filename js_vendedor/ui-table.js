function resetearFilasDetalle() {

    let filas = document.querySelectorAll("#tabla_detalle tbody tr");

    filas.forEach(fila => {

        fila.style.display = "none";

        const check = fila.querySelector(".check_producto");
        const select = fila.querySelector(".estado_producto");

        if (check) check.checked = false;

        if (select) {
            select.disabled = true;
            select.value = "";
        }
    });
}
function mostrarSoloFactura(numero) {

    let filas = document.querySelectorAll(
        `#tabla_detalles tbody tr[data-factura="${numero}"]`
    );

    console.log("Filas encontradas:", filas.length);

    filas.forEach(fila => {

        fila.style.display = "table-row";

        const check = fila.querySelector(".check_producto");

        if (!check) return;

        check.onchange = function () {

            const select = this.closest("tr")?.querySelector(".estado_producto");

            if (!select) return;

            select.disabled = !this.checked;

            if (!this.checked) {
                select.value = "";
            }
        };
    });
}