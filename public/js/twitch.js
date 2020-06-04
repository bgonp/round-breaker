;(() => {
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

    const competition_id = parseInt(form.querySelector('input[name="competition_id"]').value);

    const command = '!confirmo';

    const openMessage = `/me ¡Confirmaciones abiertas! Escribe ${command} en el chat para confirmar tu inscripción (tienes que haberte inscrito previamente a través de la web)`;

    const confirm = async (twitch_name) => {
        const data = new FormData();
        data.set('competition_id', competition_id);
        data.set('twitch_name', twitch_name);
        const response = await fetch('/api/confirm_registration', { method: 'post', body: data });
        if (response.status === 200) {
            const data = await response.json();
            const registration = document.getElementById('registration-' + data.registration_id);
            if (registration) {
                registration.classList.add('confirmed');
                registration.querySelector('input[name="confirm"]').value = 0;
                registration.querySelector('button[type="submit"]').title = 'Unconfirm';
            }
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const data = new FormData(e.target);
        client.opts.channels = [data.get('twitch_channel')];
        client.opts.identity.username = data.get('twitch_bot_name'); // roundbreaker
        client.opts.identity.password = data.get('twitch_bot_token'); // oauth:l81j2b4wknlagdmbwcgezw77v5c44b
        try {
            await client.connect();
        } catch (error) {
            alert('Wrong credentials');
        }
    });

    client.on('connected', async () => {
        const data = new FormData(form);
        const response = await fetch('/api/open_confirmations', { method: 'post', body: data });
        if (response.status !== 200) {
            client.disconnect();
            alert('You cannot do this!');
        } else {
            client.say(client.opts.channels[0], openMessage);
        }
    });

    client.on('chat', (channel, userstate, message, self) => {
        if (!self && message === command) {
            confirm(userstate['username'])
        }
    });
})();