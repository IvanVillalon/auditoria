console.log("CARGÓ AUDITOR JS");
window.verCompras = function(rut){

    fetch("vistas_auditor/obtener_compras.php?rut=" + rut)
    .then(response => response.text())
    .then(data => {
        document.getElementById("compras").innerHTML = data;
    });
}
function filtrarTabla(){
    let input = document.getElementById("buscador");
    let filtro = input.value.toLowerCase();
    let tabla = document.getElementById("tablaClientes");
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
function filtrarTablaNotaCredito(){
    let input = document.getElementById("buscadorNotaCredito");
    let filtro = input.value.toLowerCase(); 
    let tabla = document.getElementById("tablaNotaCredito");
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
function verDetalleNotaCredito(id_nota_credito) {
    fetch("vistas_auditor/obtener_detalle_nota.php?id=" + id_nota_credito)
    .then(response => response.text())
    .then(data => {
        document.getElementById("detalleNotaCredito").innerHTML = data;
    });
}
function procesarnotacredito(idNota){
    let accion = document.getElementById("accion"+idNota).value;
    let motivo = document.getElementById("textoAlerta"+idNota).value;
    let prioridad = document.getElementById("prioridad"+idNota).value;
    if (accion === "") {
        alert("Por favor, selecciona una acción para procesar la nota de crédito.");
        return;
    }
    if (confirm("¿Esta seguro de realizar esta accion?")){
        fetch("procesar_reporte_auditor.php",{
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_referencia: idNota,
                accion: accion,
                prioridad: prioridad,
                motivo_alerta: motivo,
                tipo_reporte:"nota_credito"
            })
        })
        .then(async response=>{
            let text = await response.text();
            console.log(text);
            return JSON.parse(text);
        })
        .then(data => {
            alert(data.mensaje);
            if(data.status === "success"){
                location.reload();
            }
        })
        .catch(error=>{
            console.error(error);
            alert("Error procesando nota");
        });

    }
}
function procesarreporteconteo(idConteo){
    let accion = document.getElementById("accion"+idConteo).value;
    let motivo = document.getElementById("textoAlerta"+idConteo).value;
    let prioridad= document.getElementById("prioridad"+idConteo).value;
    if(accion === ""){
        alert("Por favor, seleccione una accion para procesar el conteo.");
        return;
    }
    if(confirm("Esta seguro de realizar este reporte?")){
        fetch("procesar_reporte_auditor.php",{
            method:"POST",
            headers:{
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id_referencia: idConteo,
                accion: accion,
                prioridad: prioridad,
                motivo_alerta: motivo,
                tipo_reporte: "conteo"
            })
        })
        .then(async response=>{
            let text = await response.text();
            console.log(text);
            return JSON.parse(text);
        })
        .then(data=>{
            alert(data.mensaje);
            if(data.status === "success"){
                location.reload();
            }
        })
        .catch(error =>{
            console.error(error);
            alert("Error al procesar reporte de conteo");
        });
    }
}
window.procesarventa = function(idVenta){

    let accion = document.getElementById("accion" + idVenta).value;
    let motivo = document.getElementById("textoAlerta" + idVenta).value;
    let prioridad = document.getElementById("prioridad" + idVenta).value;

    if(accion === ""){
        alert("Selecciona una acción");
        return;
    }

    fetch("procesar_reporte_auditor.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify({
            id_referencia: idVenta,
            accion: accion,
            prioridad: prioridad,
            motivo_alerta: motivo,
            tipo_reporte: "venta"
        })
    })
    .then(response => response.json())
    .then(data => {

        alert(data.mensaje);

        if(data.status === "success"){
            location.reload();
        }

    })
    .catch(error => {
        console.error(error);
        alert("Error procesando venta");
    });
}
function seleccionarNota(idNota){

    fetch("vistas_auditor/obtener_detalle_nota.php?id=" + idNota)

    .then(response => response.text())

    .then(html => {

        let div = document.getElementById("detalleNota");

        div.style.display = "block";

        div.innerHTML = html;

        div.scrollIntoView({
            behavior: "smooth"
        });

    })

    .catch(error => {

        console.error(error);

        alert("Error al cargar detalle");

    });

}
function seleccionarproductocontado(idConteo){
    fetch("vistas_auditor/obtener_detalle_conteo.php?id="+idConteo)
    .then(response => response.text())
    .then(html =>{
        let div= document.getElementById("detalleConteo");
        div.style.display = "block";
        div.innerHTML= html;
        div.scrollIntoView({
            behavior: "smooth"
        });
    })
    .catch(error => {
        console.error(error);
        alert("Error al cargar detalle de conteo");
    });

}
function seleccionarCompra(idCompra){
    fetch("vistas_auditor/obtener_detalles_venta.php?id="+ idCompra)
    .then(response => response.text())
    .then(html =>{
        let div = document.getElementById("detalleCompra");
        div.style.display= "block";
        div.innerHTML = html;
        div.scrollIntoView({
            behavior: "smooth"
        });
    })
    .catch(error =>{
        console.error(error);
        alert("Error al cargar los detalles de las ventas");
    });
}
function mostrarAlerta(idNota){
    let select = document.getElementById("accion"+idNota);
    let div = document.getElementById("divAlerta"+idNota);   
        if (select.value === "alertar" ||  select.value === "solicitardetalle") {
            div.style.display = "block";
        } else {
            div.style.display = "none";
        }
    }



const historialVentas = document.getElementById("historialventas");
const historialClientes = document.getElementById("historialregistroclientes");
const accionesOperarios = document.getElementById("accionesoperarios");

if(historialVentas){

    historialVentas.addEventListener("click", function(){
        document.getElementById("historial_ventas").style.display = "block";
        document.getElementById("historial_registro_clientes").style.display = "none";
        document.getElementById("acciones_operarios").style.display = "none";
    });

    historialClientes.addEventListener("click", function(){
        document.getElementById("historial_ventas").style.display = "none";
        document.getElementById("historial_registro_clientes").style.display = "block";
        document.getElementById("acciones_operarios").style.display = "none";
    });

    accionesOperarios.addEventListener("click", function(){
        document.getElementById("historial_ventas").style.display = "none";
        document.getElementById("historial_registro_clientes").style.display = "none";
        document.getElementById("acciones_operarios").style.display = "block";
    });
}
function auditarConteo(idReporte){
    fetch("funciones/detalle_conteo_auditor.php?id=" + idReporte)
    .then(response => response.text())
    .then(html => {
        let div = document.getElementById("detalleConteo");

        div.style.display = "block";
        div.innerHTML = html;

        div.scrollIntoView({
            behavior: "smooth"
        });
    })
    .catch(error => {
        console.error(error);
        alert("Error al cargar detalle del conteo");
    });
}


function guardarAuditoriaConteo(idReporte){

    let decision = document.getElementById("decision" + idReporte).value;
    let observacion = document.getElementById("observacion" + idReporte).value;

    if(decision === ""){
        alert("Seleccione una decisión");
        return;
    }

    fetch("procesar_auditoria_conteo.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            id_reporte: idReporte,
            decision: decision,
            observacion: observacion
        })
    })
    .then(async response => {
        let text = await response.text();
        console.log("RESPUESTA:", text);
        return JSON.parse(text);
    })
    .then(data => {
        alert(data.mensaje);

        if(data.status === "success"){
            location.reload();
        }
    })
    .catch(error => {
        console.error(error);
        alert("Error procesando auditoría");
    });
}