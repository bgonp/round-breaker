;(() => {
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

    const gameFilterOption = document.getElementsByName('gameFilterOption');
    if (gameFilterOption.length > 0) {
        let competitionList = document.getElementById('competitionList').getElementsByTagName("li");
        for (let i = 0; i < gameFilterOption.length; i++) {
            gameFilterOption[i].addEventListener('click', (e) => {
                e.preventDefault();
                for (let j = 0; j < competitionList.length; j++) {
                    if (gameFilterOption[i].value === competitionList[j].value.toString() || gameFilterOption[i].value === "") {
                        competitionList[j].classList.remove("d-none");
                        competitionList[j].classList.add("d-flex");
                    } else {
                        competitionList[j].classList.remove("d-flex");
                        competitionList[j].classList.add("d-none");
                    }
                }
            });
        }
    }

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
    const submit = document.getElementById('submit');
      
    if (inputPassword && inputPasswordRepeat && submit){
        inputPassword.addEventListener('keyup', function(e){
            e.preventDefault();
            if (inputPassword.value ==
                inputPasswordRepeat.value) {
                    submit.disabled = false;
          } else {
            submit.disabled = true;
          }
        });
        inputPasswordRepeat.addEventListener('keyup', function(e){
            e.preventDefault();
            if (inputPassword.value ==
                inputPasswordRepeat.value) {
                    submit.disabled = false;
          } else {
            submit.disabled = true;
          }
        });
    }


})()
