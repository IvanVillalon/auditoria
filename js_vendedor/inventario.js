function procesarrespuestainventario(id_reporte) {

    const accionEl = document.querySelector(`select.accion[data-id="${id_reporte}"]`);
    const cantidadEl = document.querySelector(`input.cantidad[data-id="${id_reporte}"]`);
    const respuestaEl = document.querySelector(`input.respuesta[data-id="${id_reporte}"]`);

    const data = {
        id_reporte,
        accion: accionEl.value,
        cantidad: cantidadEl.value,
        respuesta: respuestaEl.value
    };

    fetch("procesar_respuesta_peticiones_auditor.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
    .then(async res => {
        const texto = await res.text();

        console.log("RESPUESTA CRUDA:", texto);

        return JSON.parse(texto);
    })
    .then(resp => {
        console.log("RESPUESTA PARSEADA:", resp);

        alert(resp.mensaje);

        if (resp.status === "ok") {
            location.reload();
        }
    })
    .catch(err => {
        console.error("Error en la petición", err);
        alert("Error en la petición");
    });
}
function guardarConteo(idToma){

    let conteos = [];

    document.querySelectorAll(".conteo").forEach(input => {

        if(input.value !== ""){

            conteos.push({
                id_producto: input.dataset.producto,
                color: input.dataset.color || null,
                stock_sistema: input.dataset.stock,
                stock_fisico: input.value
            });

        }

    });

    if(conteos.length === 0){
        alert("Debes ingresar al menos un conteo");
        return;
    }

    fetch("guardar_conteo.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify({
            id_toma: idToma,
            conteos: conteos
        })
    })
    .then(async r => {
        const text = await r.text();
        console.log("RESPUESTA CRUDA:", text);

        try{
            return JSON.parse(text);
        }catch(e){
            throw new Error("JSON inválido");
        }
    })
    .then(data=>{

        alert(data.mensaje);

        if(data.status === "success"){
            location.reload();
        }

    })
    .catch(err=>{
        console.error(err);
        alert("Error: " + err.message);
    });

}