function aprobarStock(idReporte){

    if(!confirm("¿Aprobar ajuste de stock?")) return;

    fetch("aprobar_stock.php",{
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body:JSON.stringify({
            id_reporte:idReporte
        })
    })
    .then(r=>r.json())
    .then(data=>{
        alert(data.mensaje);

        if(data.status==="ok"){
            location.reload();
        }
    });
}
function aprobarReporte(idReporte){

    if(!confirm("¿Aprobar este ajuste?")) return;

    fetch("aprobar_reporte_admin.php", {
        method:"POST",
        headers:{
            "Content-Type":"application/json"
        },
        body: JSON.stringify({
            id_reporte:idReporte
        })
    })
    .then(r=>r.json())
    .then(data=>{
        alert(data.mensaje);

        if(data.status === "ok"){
            location.reload();
        }
    })
    .catch(error=>{
        console.error(error);
        alert("Error");
    });

}