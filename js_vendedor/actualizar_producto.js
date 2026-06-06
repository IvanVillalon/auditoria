document.addEventListener("DOMContentLoaded", () => {

    const selectProducto =
        document.getElementById("producto_actualizar");

    if (!selectProducto) return;

    selectProducto.addEventListener("change", async function () {

        const id = this.value;

        if (!id) return;

        try {

            const response =
                await fetch(`api/obtener_producto.php?id=${id}`);

            const data = await response.json();

            if (data.status !== "ok") {
                alert(data.mensaje);
                return;
            }

            const producto = data.producto;

            document.getElementById("precio_actual").textContent =
                producto.valor_unitario;

            document.getElementById("categoria_actual").textContent =
                producto.categoria_producto;

            document.getElementById("medicion_actual").textContent =
                producto.categoria_medicion;

            document.getElementById("id_producto_hidden").value =
                producto.id;

        } catch (error) {

            console.error(error);

            alert("Error al cargar producto");
        }
    });
});