const buttons = document.getElementsByClassName('bracket-button');

if (buttons.length > 0) {
    for (const button of buttons) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('ROUND ' + this.dataset.round);
            console.log('TEAM ' + this.dataset.team);
        });
    }
}

const setRoundWinner = (round_id, team_id) => {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // TODO
        }
    }
    xhr.open("PUT", endpoint, true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send(`round_id=${round_id}&team_id=${team_id}`);
}