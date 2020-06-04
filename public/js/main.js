;(() => {
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

    const bracketFull = document.querySelector('.bracket .bracket-zoom');
    if (bracketFull) bracketFull.addEventListener('click', (e) => {
        e.preventDefault();
        document.body.classList.toggle('fullscreen-bracket');
    });
})()