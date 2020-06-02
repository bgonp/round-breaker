;(() => {
    const endpoint = '/api/set_round_winner';

    const buttons = document.getElementsByClassName('bracket-button');

    if (buttons.length > 0) {
        for (const button of buttons) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                setWinner(this.dataset.round, this.dataset.team);
            });
        }
    }

    const setWinner = (round_id, team_id) => {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText);
            }
        }
        xhr.open("PUT", endpoint, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send(`round_id=${round_id}&team_id=${team_id}`);
    }
})();