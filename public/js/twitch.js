const conf = {
    options: {
        debug: true // TODO
    },
    connection: {
        reconnect: true,
        secure: true
    },
    identity: {}
};

const client = new tmi.client(conf);

const form = document.getElementById('twitch_params');

const competition_id = form.querySelector('input[name="competition_id"]').value;

const endpoint = '/api/confirm_registration';

const openMessage = "/me ¡Confirmaciones abiertas! Escribe !confirmo en el chat para confirmar tu inscripción (tienes que haberte inscrito previamente a través de la web)";

const confirmRegistration = (twitch_name) => {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            const registration = document.getElementById('registration-' + this.responseText);
            if (registration) registration.classList.add('confirmed');
        }
    }
    xhr.open("PUT", endpoint, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(`competition_id=${competition_id}&twitch_name=${twitch_name}`);
}

form.addEventListener('submit', e => {
    const data = new FormData(e.target);
    client.opts.channels = [data.get('channel')];
    client.opts.identity.username = data.get('username'); // roundbreaker
    client.opts.identity.password = data.get('password'); // oauth:l81j2b4wknlagdmbwcgezw77v5c44b
    client.connect();
    e.preventDefault();
});

client.on('connected', () => {
    client.say(client.opts.channels[0], openMessage);
});

client.on('chat', (channel, userstate, message, self) => {
    if (!self && message === '!confirmo') {
        confirmRegistration(userstate['username'])
    }
});