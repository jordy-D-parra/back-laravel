document.addEventListener('DOMContentLoaded', function() {

    // Capturar el formulario
    const form = document.querySelector('form');

    if (form) {
        form.addEventListener('submit', function(e) {
            // Opcional: Mostrar los datos antes de enviar
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            console.log('Intentando login con:', email);

            // Aquí puedes agregar validaciones si quieres
            if (email.trim() === '' || password.trim() === '') {
                e.preventDefault();
                alert('Por favor completa todos los campos');
            }

            // Si no hay error, el formulario se envía normalmente
        });
    }

    // Animación simple en los inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#667eea';
        });

        input.addEventListener('blur', function() {
            this.style.borderColor = '#ddd';
        });
    });

    console.log('✅ Login JS cargado correctamente');
});
