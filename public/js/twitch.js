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

const container = document.getElementById('messages');

document.getElementById('twitch_params').addEventListener('submit', e => {
    const data = new FormData(e.target);
    client.opts.channels = [data.get('channel')];
    client.opts.identity.username = data.get('username');
    client.opts.identity.password = data.get('password');
    client.connect().then(() => {
        container.innerHTML += '<li>Listening <strong>'+data.get('channel')+'</strong></li>';
        document.getElementById('twitch_params').remove();
    }).catch((err) => {
        container.innerHTML += '<li><strong>ERROR</strong> - Refresh the page and try again</li>';
        document.getElementById('twitch_params').remove();
    });
    e.preventDefault();
});

client.on('chat', (channel, userstate, message, self) => {
    container.innerHTML += '<li><strong>'+userstate['display-name']+'</strong>: '+message+'</li>';
});