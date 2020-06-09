;(() => {
    document.querySelector('.main-menu .navbar-toggler').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('main-menu').classList.toggle('show');
    })

    const confirmables = document.querySelectorAll('a.confirmable, button.confirmable, input.confirmable');
    for (const confirmable of confirmables) {
        confirmable.addEventListener('click', (e) => {
            if (!confirm('¿Estas seguro?')) e.preventDefault();
        });
    }

    const closeButton = document.querySelector('.message-container .close');
    if (closeButton) closeButton.addEventListener('click', (e) => {
        e.preventDefault();
        document.querySelector('.message-container').remove();
    });

    const loginTab = document.getElementById('login-tab');
    if (loginTab) loginTab.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('login-tab').classList.add('active');
        document.getElementById('register-tab').classList.remove('active');
        document.getElementById('login-form').classList.remove('d-none');
        document.getElementById('register-form').classList.add('d-none');
    });

    const registerTab = document.getElementById('register-tab');
    if (registerTab) registerTab.addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('login-tab').classList.remove('active');
        document.getElementById('register-tab').classList.add('active');
        document.getElementById('login-form').classList.add('d-none');
        document.getElementById('register-form').classList.remove('d-none');
    });

    const filterByGame = document.getElementById('filter-by-game');
    if (filterByGame) filterByGame.addEventListener('change', () => {
        location.href = location.pathname + (filterByGame.value ? '?game=' + filterByGame.value : '');
    });

    const bracketFull = document.querySelector('.bracket .bracket-zoom');
    if (bracketFull) bracketFull.addEventListener('click', (e) => {
        e.preventDefault();
        document.body.classList.toggle('fullscreen-bracket');
    });

    const inputOpen = document.querySelector('.close-competition #inputOpen');
    if (inputOpen) inputOpen.addEventListener('click', (e) => {
        if (inputOpen.checked && !confirm('Si reabres una competición cerrada se borrarán los equipos y el progreso. ¿Estas seguro?')) {
            e.preventDefault();
        }
    });

    const inputPassword = document.getElementById('inputPassword');
    const inputPasswordRepeat = document.getElementById('inputPasswordRepeat');
    const submitRegister = document.getElementById('submit-register');
      
    if (inputPassword && inputPasswordRepeat && submitRegister){
        inputPassword.addEventListener('input', () =>
            submitRegister.disabled = inputPassword.value !== inputPasswordRepeat.value
        );
        inputPasswordRepeat.addEventListener('input', () =>
            submitRegister.disabled = inputPassword.value !== inputPasswordRepeat.value
        );
    }

})()
