
const formProducto = document.getElementById("form_producto");

if (formProducto) {

    formProducto.addEventListener("submit", function(e) {

        e.preventDefault();

        let formData = new FormData(this);

        fetch("api/registro_producto.php", {
            method: "POST",
            body: formData
        })
        .then(res=>res.json())
        .then(data => {

            console.log(data);

            if (data.status === "success") {
                alert(data.mensaje);
                window.location.href = "vendedor.php";
            } else {
                alert("Error: " + data.mensaje);
            }

        })
        .catch(error => {
            console.error("Error:", error);
        });

    });

}