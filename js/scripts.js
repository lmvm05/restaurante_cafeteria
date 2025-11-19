// Obtiene el modal y la imagen
var modal = document.getElementById("imageModal");
var modalImg = document.getElementById("modalImage");

// Función para abrir el modal
function openModal(imageSrc) {
    modal.style.display = "block";
    modalImg.src = imageSrc;
    // Opcional: Deshabilitar el scroll del body mientras el modal está abierto
    document.body.style.overflow = "hidden";
}

// Función para cerrar el modal
function closeModal() {
    modal.style.display = "none";
    // Opcional: Restaurar el scroll del body
    document.body.style.overflow = "auto";
}

// Cierra el modal si el usuario hace clic fuera de la imagen
modal.onclick = function(event) {
    // Si el clic fue en el fondo del modal (no en la imagen ni la X)
    if (event.target == modal) {
        closeModal();
    }
}
document.addEventListener('DOMContentLoaded', (event) => {
    // Código existente del Modal (Lightbox) también va aquí...
    // ...

    // Lógica del formulario de reservación
    const form = document.getElementById('reservationForm');
    const message = document.getElementById('message');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 

            // **1. Validación Básica (que la fecha no sea en el pasado)**
            const fechaInput = document.getElementById('fecha').value;
            const fechaReserva = new Date(fechaInput);
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            if (fechaReserva < hoy) {
                showMessage("La fecha de reserva no puede ser en el pasado.", "error");
                return;
            }

            // **2. Envío de Datos al Backend (AJAX)**
            const formData = new FormData(this);
            
            // La URL del script PHP que guarda en la BD
            fetch('guardar_reservacion.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // El PHP debe devolver una respuesta JSON
            .then(data => {
                if (data.success) {
                    showMessage("¡Reservación confirmada! Recibirás un correo electrónico de confirmación.", "success");
                    this.reset(); // Limpia el formulario
                } else {
                    // Muestra el error devuelto por el servidor
                    showMessage("Error al guardar la reservación: " + data.message, "error");
                }
            })
            .catch(error => {
                console.error('Error de red o del servidor:', error);
                showMessage("Ocurrió un error inesperado al contactar al servidor. Revisa la consola para más detalles.", "error");
            });
        });
    }

    // Función para mostrar mensajes de éxito/error
    function showMessage(text, type) {
        message.textContent = text;
        message.className = type;
        message.classList.remove('hidden');
        
        setTimeout(() => {
            message.classList.add('hidden');
        }, 5000);
    }
});