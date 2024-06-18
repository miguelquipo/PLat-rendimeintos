//MODOS
document.addEventListener('DOMContentLoaded', function () {
    // ... (tu código existente)

    // Agrega un evento de cambio para el interruptor de día y noche
    const dayNightSwitch = document.getElementById('dayNightSwitch');
    dayNightSwitch.addEventListener('change', toggleDayNightMode);

    // Verifica el estado guardado en localStorage al cargar la página
    const isNightMode = JSON.parse(localStorage.getItem('isNightMode')) || true; // Inicia en modo obscuro
    dayNightSwitch.checked = isNightMode;

    // Aplica el modo día o noche al cargar la página
    applyDayNightMode(isNightMode);
});
function toggleNightMode() {
    const nightModeSwitch = document.getElementById('nightModeSwitch');
    applyDayNightMode(nightModeSwitch.checked);
}

function toggleDayNightMode() {
    const dayNightSwitch = document.getElementById('dayNightSwitch');
    const isNightMode = dayNightSwitch.checked;

    // Guarda el estado en localStorage
    localStorage.setItem('isNightMode', JSON.stringify(isNightMode));

    // Aplica el modo día o noche
    applyDayNightMode(isNightMode);
}

function applyDayNightMode(isNightMode) {
    const body = document.body;
    const settingsDropdown = document.querySelector('.settings-dropdown');
    const clock = document.querySelector('.clock');
    
    // Aplica estilos diferentes para el modo día y noche
    if (isNightMode) {
        body.classList.add('night-mode');
        settingsDropdown.classList.add('night-mode');
        clock.classList.add('night-mode');

        // Si se está cambiando a modo noche, quita la clase de modo día
        body.classList.remove('day-mode');
    } else {
        body.classList.remove('night-mode');
        settingsDropdown.classList.remove('night-mode');
        clock.classList.remove('night-mode');

        // Si se está cambiando a modo día, agrega la clase de modo día
        body.classList.add('day-mode');
    }
}

