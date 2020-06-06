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

    const setWinner = async (round_id, team_id) => {
        const requestData = new FormData();
        requestData.set('round_id', round_id);
        requestData.set('team_id', team_id);
        const response = await fetch(endpoint, { method: 'post', body: requestData });
        const data = await response.json();
        if (response.status === 200) {
            const inputFinished = document.getElementById('inputFinished');
            updateRound(data.origin.round_id, data.origin.teams, data.origin.winner);
            if (data.destination)
                updateRound(data.destination.round_id, data.destination.teams, data.destination.winner);
            if (data.finished)
                inputFinished.setAttribute('checked', 'checked');
            else
                inputFinished.removeAttribute('checked');
        } else {
            alert(data.message ?? 'Ocurrió un error');
        }
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