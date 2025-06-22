document.addEventListener('DOMContentLoaded', function() {
    var filas = document.querySelectorAll(".row-consejos");
    for(var i=0; i<filas.length; i++){
        if(i%2==0){
            filas[i].classList.add('color-fondo');
        }
    }

    const btnSumar = document.getElementById('btn_sumar');
    const btnRestar = document.getElementById('btn_restar');
    const cantidadViajerosSpan = document.getElementById('cantidad_viajeros');
    const precioUnitarioTourElement = document.getElementById('precio_unitario_tour_oculto');
    const btnReservar = document.getElementById('btn_reservar');

    /*si elvalor de las variables no se pudo asignar, entonces le asigno null  
    si todo fue bien, entonces las recojo*/
    const tourId = typeof tourIdFromPHP !== 'undefined' ? tourIdFromPHP : null;
    const tourName = typeof tourNameFromPHP !== 'undefined' ? tourNameFromPHP : null;
    let precioUnitario = 0;
    //accedo a la propiedad data-precio del elemento 
    if (precioUnitarioTourElement && precioUnitarioTourElement.dataset.precio) {
        precioUnitario = parseFloat(precioUnitarioTourElement.dataset.precio);
    } else {
        if (btnReservar) {
            btnReservar.style.pointerEvents = 'none';
            btnReservar.setAttribute('href', '#');
            btnReservar.addEventListener('click', function(event) {
                event.preventDefault();
                alert('No se pudo cargar el precio del tour.');
            });
        }
    }
    
    let cantidadViajeros = 1;
    if (cantidadViajerosSpan) { 
        //me aseguro que siempre como minimo el num viajeros sea 1 
        cantidadViajeros = parseInt(cantidadViajerosSpan.textContent) || 1;
    }
    //deshabilitar el boton restar si la cantidad de viajeros es 1 y habilitarlo si es mayor
    function actualizarEstadoBotonRestar() {
        if (btnRestar){
            btnRestar.disabled = cantidadViajeros <= 1;
        }
    }

    /*funcion para redirigir al usuario cuando de click en RESERVAR de los tours individuales**/
    function actualizarBotonReservarHref(){
        if (btnReservar && tourId && tourName && precioUnitario > 0) {
            const precioTotal = cantidadViajeros * precioUnitario;
            //se verifica que el usuario haya iniciado sesión y lo redirige al formulario de reserva pasando por la url datos de la misma
            //si se pasó correctamente el valor del usuario de la sesion entonces se mantiene su valor y no se asigna null 
            if (typeof isUserLoggedInFromPHP !== 'undefined' && isUserLoggedInFromPHP) { 
                btnReservar.href = `/reserva_form.php?tour_id=${tourId}&cantidad=${cantidadViajeros}&precioTotal=${precioTotal}&precio_unitario=${precioUnitario}&tourName=${encodeURIComponent(tourName)}`;
                btnReservar.style.pointerEvents = 'auto';
            } else {
                //si no se ha logueado no puede hacer la reserva, entonces se redirge a login.php
                const targetReservationUrl = `/reserva_form.php?message=necesita_login&tour_id=${tourId}&cantidad=${cantidadViajeros}&precioTotal=${precioTotal}&tourName=${encodeURIComponent(tourName)}&precio_unitario=${precioUnitario}`
                btnReservar.href = `../login.php?action=login&redirect_to=${encodeURIComponent(targetReservationUrl)}`; 
                btnReservar.style.pointerEvents = 'auto';
            }
        } else if (btnReservar) {
            btnReservar.style.pointerEvents = 'none';
            btnReservar.setAttribute('href', '#');
        }
    }

    if (btnSumar) {
        btnSumar.addEventListener('click', function() {
            cantidadViajeros++;
            if (cantidadViajerosSpan) {
                cantidadViajerosSpan.textContent = cantidadViajeros;
            }
            actualizarEstadoBotonRestar();
            actualizarBotonReservarHref();
        });
    }

    if (btnRestar) {
        //para decremetar la cantidad de viajeros
        btnRestar.addEventListener('click', function() {
            if (cantidadViajeros > 1) {
                cantidadViajeros--;
                if (cantidadViajerosSpan) {
                    cantidadViajerosSpan.textContent = cantidadViajeros;
                }
                actualizarEstadoBotonRestar();
                actualizarBotonReservarHref();
            }
        });
    }

    if (cantidadViajerosSpan) {
        cantidadViajerosSpan.textContent = cantidadViajeros;
    }

    actualizarEstadoBotonRestar();
    actualizarBotonReservarHref(); 
    //select para las opciones que tiene el usuario logueado
    const select = document.getElementById("userOptionsSelect");
    if (select){ 
        //cada vez que cambie, la url será el valor seleccionado
        select.addEventListener("change", function () {
            const value = this.value;
            if (value){
                window.location.href = value;
            }
        });
    }
});