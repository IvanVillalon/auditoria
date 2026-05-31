let idVentaSeleccionada = null;

function seleccionarFactura(numero, idVenta) {

    idVentaSeleccionada = idVenta;

    mostrarTablasNotaCredito();
    resetearFilasDetalle();
    mostrarSoloFactura(numero);
}