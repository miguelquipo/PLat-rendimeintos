// fullscreen.js

// Recupera el estado del botón y del modo de pantalla completa desde sessionStorage al cargar la página
let isRunning = JSON.parse(sessionStorage.getItem('isRunning')) || false;
let isFullscreen = JSON.parse(sessionStorage.getItem('isFullscreen')) || false;

document.addEventListener('DOMContentLoaded', function () {
  const button = document.getElementById('startStopButton');

  // Verifica el estado recuperado y ajusta la lógica del botón
  if (isRunning) {
    // Si está iniciado, muestra el estado como FINALIZAR
    button.textContent = 'FINALIZAR';
    button.classList.add('finished');
    // Si está en pantalla completa, activa el modo pantalla completa
    if (isFullscreen) {
      enterFullscreen();
    }
  } else {
    // Si está finalizado o el estado no se ha guardado previamente, muestra el estado como INICIAR
    button.textContent = 'INICIAR';
    button.classList.remove('finished');
  }
});

function toggleStartStop() {
  const button = document.getElementById('startStopButton');

  if (isRunning) {
    // Lógica para FINALIZAR
    alert('Actividades finalizadas');
    button.textContent = 'INICIAR';
    button.classList.remove('finished');

    // Desactivar el modo pantalla completa
    exitFullscreen();
    // Cambiar la dirección del menú en index.html a ./PAGES/WhaitingPage.html
    sessionStorage.setItem('menuLink', './PAGES/WhaitingPage.html');
  } else {
    // Lógica para INICIAR
    alert('Actividades iniciadas');
    button.textContent = 'FINALIZAR';
    button.classList.add('finished');

    // Activar el modo pantalla completa
    enterFullscreen();
    // Restaurar la dirección del menú en index.html a rendimientos.html
    sessionStorage.setItem('menuLink', 'rendimientos.html');
  }

  // Cambiar el estado de ejecución
  isRunning = !isRunning;

  // Realizar la petición AJAX al archivo PHP solo cuando el botón está iniciado
  if (isRunning) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "./PHP/InsertInicio.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Envía la información necesaria (puede ser vacía si no se requieren parámetros)
    xhr.send("isRunning=true");

    // Maneja la respuesta del servidor (opcional)
    xhr.onreadystatechange = function () {
      if (xhr.readyState == 4 && xhr.status == 200) {
        // La respuesta del servidor (puede realizar alguna acción si es necesario)
        console.log(xhr.responseText);
      }
    };
  }

  // Guarda el estado del botón y del modo de pantalla completa en sessionStorage
  sessionStorage.setItem('isRunning', JSON.stringify(isRunning));
  sessionStorage.setItem('isFullscreen', JSON.stringify(isFullscreen));

  // Actualizar el texto y la clase del botón
  button.textContent = isRunning ? 'FINALIZAR' : 'INICIAR';
  button.classList.toggle('finished', isRunning);
}

// Función para activar el modo pantalla completa
function enterFullscreen() {
  const element = document.documentElement;
  if (element.requestFullscreen) {
    element.requestFullscreen();
  } else if (element.mozRequestFullScreen) {
    element.mozRequestFullScreen();
  } else if (element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  } else if (element.msRequestFullscreen) {
    element.msRequestFullscreen();
  }
}

// Función para desactivar el modo pantalla completa
function exitFullscreen() {
  if (document.exitFullscreen) {
    document.exitFullscreen();
  } else if (document.mozCancelFullScreen) {
    document.mozCancelFullScreen();
  } else if (document.webkitExitFullscreen) {
    document.webkitExitFullscreen();
  } else if (document.msExitFullscreen) {
    document.msExitFullscreen();
  }
}
