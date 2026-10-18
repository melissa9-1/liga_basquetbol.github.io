const authView = document.getElementById('authView');
const appView = document.getElementById('appView');
const logoutBtn = document.getElementById('logoutBtn');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const tabs = document.querySelectorAll('.tab');

const teamForm = document.getElementById('teamForm');
const playerForm = document.getElementById('playerForm');
const matchForm = document.getElementById('matchForm');
const teamsList = document.getElementById('teamsList');
const playersList = document.getElementById('playersList');
const matchesList = document.getElementById('matchesList');
const statsContent = document.getElementById('statsContent');
const playerTeamSelect = document.getElementById('playerTeamSelect');
const localTeamSelect = document.getElementById('localTeamSelect');
const visitorTeamSelect = document.getElementById('visitorTeamSelect');

const state = {
  teams: [],
  players: {},
  matches: [],
  stats: { standings: [], leaders: [], recentMatches: [] },
};

async function apiRequest(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    },
    ...options,
  });

  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    throw new Error(data.message || 'Ocurrió un error inesperado.');
  }

  return data;
}

function setActiveTab(tabName) {
  tabs.forEach((button) => {
    const isCurrent = button.dataset.tab === tabName;
    button.classList.toggle('active', isCurrent);
  });

  loginForm.classList.toggle('hidden', tabName !== 'login');
  registerForm.classList.toggle('hidden', tabName !== 'register');
}

function clearTeamForm() {
  teamForm.reset();
  teamForm.querySelector('[name="teamId"]').value = '';
}

function clearPlayerForm() {
  playerForm.reset();
  playerForm.querySelector('[name="playerId"]').value = '';
}

async function checkSession() {
  try {
    const data = await apiRequest('/api/session');
    if (data.authenticated) {
      authView.classList.add('hidden');
      appView.classList.remove('hidden');
      logoutBtn.classList.remove('hidden');
      await loadDashboard();
      return;
    }
  } catch (error) {
    console.error(error);
  }

  authView.classList.remove('hidden');
  appView.classList.add('hidden');
  logoutBtn.classList.add('hidden');
}

async function loadDashboard() {
  const [teams, matches, stats] = await Promise.all([
    apiRequest('/api/teams'),
    apiRequest('/api/matches'),
    apiRequest('/api/stats'),
  ]);

  state.teams = teams;
  state.matches = matches;
  state.stats = stats;

  populateTeamSelects();
  renderTeams();
  renderPlayers();
  renderMatches();
  renderStats();
}

function populateTeamSelects() {
  const options = state.teams
    .map((team) => `<option value="${team.id}">${team.name} (${team.city})</option>`)
    .join('');

  playerTeamSelect.innerHTML = `<option value="">Seleccione un equipo</option>${options}`;
  localTeamSelect.innerHTML = `<option value="">Equipo local</option>${options}`;
  visitorTeamSelect.innerHTML = `<option value="">Equipo visitante</option>${options}`;

  if (state.teams.length > 0) {
    if (!playerTeamSelect.value) {
      playerTeamSelect.value = String(state.teams[0].id);
    }
    if (!localTeamSelect.value) {
      localTeamSelect.value = String(state.teams[0].id);
    }
    if (!visitorTeamSelect.value || visitorTeamSelect.value === localTeamSelect.value) {
      visitorTeamSelect.value = String(state.teams[1]?.id || state.teams[0].id);
    }
  }
}

async function renderTeams() {
  if (!state.teams.length) {
    teamsList.innerHTML = '<div class="empty">Aún no hay equipos registrados.</div>';
    return;
  }

  teamsList.innerHTML = state.teams
    .map(
      (team) => `
        <div class="item">
          <div>
            <strong>${team.name}</strong>
            <small>${team.city}</small>
          </div>
          <div class="actions">
            <button class="secondary" data-team-edit="${team.id}">Editar</button>
            <button class="danger" data-team-delete="${team.id}">Eliminar</button>
          </div>
        </div>
      `,
    )
    .join('');
}

async function renderPlayers() {
  const selectedTeamId = playerTeamSelect.value || state.teams[0]?.id;
  if (!selectedTeamId) {
    playersList.innerHTML = '<div class="empty">Selecciona un equipo para ver sus jugadores.</div>';
    return;
  }

  try {
    const players = await apiRequest(`/api/teams/${selectedTeamId}/players`);
    state.players[selectedTeamId] = players;

    if (!players.length) {
      playersList.innerHTML = '<div class="empty">Este equipo aún no tiene jugadores.</div>';
      return;
    }

    playersList.innerHTML = players
      .map(
        (player) => `
          <div class="item">
            <div>
              <strong>${player.name}</strong>
              <small>${player.position} • #${player.number}</small>
            </div>
            <div class="actions">
              <button class="secondary" data-player-edit="${player.id}">Editar</button>
              <button class="danger" data-player-delete="${player.id}">Eliminar</button>
            </div>
          </div>
        `,
      )
      .join('');
  } catch (error) {
    playersList.innerHTML = `<div class="empty">${error.message}</div>`;
  }
}

async function renderMatches() {
  if (!state.matches.length) {
    matchesList.innerHTML = '<div class="empty">Aún no se han registrado partidos.</div>';
    return;
  }

  matchesList.innerHTML = state.matches
    .map(
      (match) => `
        <div class="item">
          <div>
            <strong>${match.local_name} ${match.local_score} - ${match.visitor_score} ${match.visitor_name}</strong>
            <small>${new Date(match.played_on).toLocaleDateString('es-ES')} • ${match.local_city} vs ${match.visitor_city}</small>
          </div>
          <div class="actions">
            <button class="danger" data-match-delete="${match.id}">Eliminar</button>
          </div>
        </div>
      `,
    )
    .join('');
}

function renderStats() {
  const { standings, recentMatches } = state.stats;

  const standingsHtml = standings.length
    ? `
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Equipo</th>
              <th>J</th>
              <th>G</th>
              <th>P</th>
              <th>PF</th>
              <th>PC</th>
              <th>Dif.</th>
            </tr>
          </thead>
          <tbody>
            ${standings
              .map(
                (team) => `
                  <tr>
                    <td>${team.name}</td>
                    <td>${team.games}</td>
                    <td>${team.wins}</td>
                    <td>${team.losses}</td>
                    <td>${team.pointsFor}</td>
                    <td>${team.pointsAgainst}</td>
                    <td>${team.pointDifference}</td>
                  </tr>
                `,
              )
              .join('')}
          </tbody>
        </table>
      </div>
    `
    : '<div class="empty">No hay estadísticas disponibles.</div>';

  const recentMatchesHtml = recentMatches.length
    ? recentMatches
        .map(
          (match) => `
            <div class="item">
              <div>
                <strong>${match.local_name} ${match.local_score} - ${match.visitor_score} ${match.visitor_name}</strong>
                <small>${new Date(match.played_on).toLocaleDateString('es-ES')}</small>
              </div>
            </div>
          `,
        )
        .join('')
    : '<div class="empty">No hay partidos recientes.</div>';

  statsContent.innerHTML = `
    <div class="stats-grid">
      <div>
        <h3>Clasificación</h3>
        ${standingsHtml}
      </div>
      <div>
        <h3>Partidos recientes</h3>
        ${recentMatchesHtml}
      </div>
    </div>
  `;
}

async function handleLogin(event) {
  event.preventDefault();
  const formData = new FormData(loginForm);
  const payload = Object.fromEntries(formData.entries());

  try {
    await apiRequest('/api/login', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    await checkSession();
  } catch (error) {
    alert(error.message);
  }
}

async function handleRegister(event) {
  event.preventDefault();
  const formData = new FormData(registerForm);
  const payload = Object.fromEntries(formData.entries());

  try {
    await apiRequest('/api/register', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
    setActiveTab('login');
    registerForm.reset();
    await checkSession();
  } catch (error) {
    alert(error.message);
  }
}

async function handleLogout() {
  try {
    await apiRequest('/api/logout', { method: 'POST' });
    clearTeamForm();
    clearPlayerForm();
    await checkSession();
  } catch (error) {
    console.error(error);
  }
}

async function handleTeamSubmit(event) {
  event.preventDefault();
  const formData = new FormData(teamForm);
  const payload = Object.fromEntries(formData.entries());
  const teamId = payload.teamId;

  try {
    if (teamId) {
      await apiRequest(`/api/teams/${teamId}`, {
        method: 'PUT',
        body: JSON.stringify({ name: payload.name, city: payload.city }),
      });
    } else {
      await apiRequest('/api/teams', {
        method: 'POST',
        body: JSON.stringify({ name: payload.name, city: payload.city }),
      });
    }

    clearTeamForm();
    await loadDashboard();
  } catch (error) {
    alert(error.message);
  }
}

async function handlePlayerSubmit(event) {
  event.preventDefault();
  const formData = new FormData(playerForm);
  const payload = Object.fromEntries(formData.entries());
  const playerId = payload.playerId;

  try {
    if (playerId) {
      await apiRequest(`/api/players/${playerId}`, {
        method: 'PUT',
        body: JSON.stringify({
          teamId: Number(payload.teamId),
          name: payload.name,
          position: payload.position,
          number: Number(payload.number),
        }),
      });
    } else {
      await apiRequest('/api/players', {
        method: 'POST',
        body: JSON.stringify({
          teamId: Number(payload.teamId),
          name: payload.name,
          position: payload.position,
          number: Number(payload.number),
        }),
      });
    }

    clearPlayerForm();
    await loadDashboard();
  } catch (error) {
    alert(error.message);
  }
}

async function handleMatchSubmit(event) {
  event.preventDefault();
  const formData = new FormData(matchForm);
  const payload = Object.fromEntries(formData.entries());

  try {
    await apiRequest('/api/matches', {
      method: 'POST',
      body: JSON.stringify({
        localTeamId: Number(payload.localTeamId),
        visitorTeamId: Number(payload.visitorTeamId),
        localScore: Number(payload.localScore),
        visitorScore: Number(payload.visitorScore),
        playedOn: payload.playedOn,
      }),
    });

    matchForm.reset();
    matchForm.querySelector('[name="localScore"]').value = 0;
    matchForm.querySelector('[name="visitorScore"]').value = 0;
    await loadDashboard();
  } catch (error) {
    alert(error.message);
  }
}

tabs.forEach((button) => {
  button.addEventListener('click', () => setActiveTab(button.dataset.tab));
});

loginForm.addEventListener('submit', handleLogin);
registerForm.addEventListener('submit', handleRegister);
logoutBtn.addEventListener('click', handleLogout);
teamForm.addEventListener('submit', handleTeamSubmit);
playerForm.addEventListener('submit', handlePlayerSubmit);
matchForm.addEventListener('submit', handleMatchSubmit);

document.addEventListener('click', async (event) => {
  const teamEditId = event.target.dataset.teamEdit;
  if (teamEditId) {
    const team = state.teams.find((item) => String(item.id) === teamEditId);
    if (!team) return;
    teamForm.querySelector('[name="teamId"]').value = team.id;
    teamForm.querySelector('[name="name"]').value = team.name;
    teamForm.querySelector('[name="city"]').value = team.city;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  const teamDeleteId = event.target.dataset.teamDelete;
  if (teamDeleteId) {
    if (!confirm('¿Deseas eliminar este equipo?')) return;
    await apiRequest(`/api/teams/${teamDeleteId}`, { method: 'DELETE' });
    await loadDashboard();
    return;
  }

  const playerEditId = event.target.dataset.playerEdit;
  if (playerEditId) {
    const teamId = playerTeamSelect.value;
    const players = state.players[teamId] || [];
    const player = players.find((item) => String(item.id) === playerEditId);
    if (!player) return;

    playerForm.querySelector('[name="playerId"]').value = player.id;
    playerForm.querySelector('[name="teamId"]').value = String(player.team_id);
    playerForm.querySelector('[name="name"]').value = player.name;
    playerForm.querySelector('[name="position"]').value = player.position;
    playerForm.querySelector('[name="number"]').value = player.number;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  const playerDeleteId = event.target.dataset.playerDelete;
  if (playerDeleteId) {
    if (!confirm('¿Deseas eliminar este jugador?')) return;
    await apiRequest(`/api/players/${playerDeleteId}`, { method: 'DELETE' });
    await renderPlayers();
    return;
  }

  const matchDeleteId = event.target.dataset.matchDelete;
  if (matchDeleteId) {
    if (!confirm('¿Deseas eliminar este partido?')) return;
    await apiRequest(`/api/matches/${matchDeleteId}`, { method: 'DELETE' });
    await loadDashboard();
  }
});

document.getElementById('resetTeamForm').addEventListener('click', clearTeamForm);
playerTeamSelect.addEventListener('change', renderPlayers);

checkSession();
