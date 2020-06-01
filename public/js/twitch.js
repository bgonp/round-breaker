const conf = {
    options: {
        debug: false
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

const confirm = (twitch_name) => {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = () => {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById('registration-' + this.responseText).classList.add('confirmed')
        }
        xhr.open("PUT", endpoint, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send(`competition_id=${competition_id}&twitch_name=${twitch_name}`);
    }
}

form.addEventListener('submit', e => {
    const data = new FormData(e.target);
    client.opts.channels = [data.get('channel')];
    client.opts.identity.username = data.get('username');
    client.opts.identity.password = data.get('password');
    client.connect().then(() => {
        // TODO
        alert('open');
    }).catch((err) => {
        // TODO
        alert('error')
    });
    e.preventDefault();
});

client.on('chat', (channel, userstate, message, self) => {
    if (!self && message === '!confirmo') {
        confirm(userstate['username'])
    }
});