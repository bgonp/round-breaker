;(() => {
    const endpoint = '/api/set_round_winner';

    const buttons = document.getElementsByClassName('bracket-button');

    if (buttons.length > 0) {
        for (const button of buttons) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (!this.dataset.round || !this.dataset.team) return;
                setWinner(this.dataset.round, this.dataset.team);
            });
        }
    }

    const setWinner = (round_id, team_id) => {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (this.readyState == 4) {
                const response = JSON.parse(this.responseText);
                if (this.status == 200) {
                    const inputFinished = document.getElementById('inputFinished');
                    if (response.origin)
                        updateRound(response.origin.round_id, response.origin.teams, response.origin.winner);
                    if (response.destination)
                        updateRound(response.destination.round_id, response.destination.teams, response.destination.winner);
                    if (response.finished)
                        inputFinished.setAttribute('checked', 'checked');
                    else
                        inputFinished.removeAttribute('checked');
                } else {
                    alert(response.message);
                }
            }
        }
        xhr.open("PUT", endpoint, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send(`round_id=${round_id}&team_id=${team_id}`);
    }

    const updateRound = (round, teams, winner) => {
        const roundTeams = document.querySelectorAll(`.bracket-button[data-round="${round}"]`);
        for (let i = 0; i < roundTeams.length; i++) {
            const teamId = i < teams.length ? teams[i] : 0;
            const teamTitle = teamId ? document.querySelector(`.bracket-button[data-team="${teamId}"]`).title : ''
            roundTeams[i].dataset.team = teamId;
            roundTeams[i].title = teamTitle;
            roundTeams[i].innerText = teamTitle;
            if (winner === teamId) roundTeams[i].parentElement.classList.add('win')
            else roundTeams[i].parentElement.classList.remove('win');
            if (winner && winner !== teamId) roundTeams[i].parentElement.classList.add('lose')
            else roundTeams[i].parentElement.classList.remove('lose');
        }
    }
})();