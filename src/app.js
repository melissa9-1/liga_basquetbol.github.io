import express from 'express';
import path from 'node:path';
import crypto from 'node:crypto';
import { fileURLToPath } from 'node:url';
import { DatabaseSync } from 'node:sqlite';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const publicDir = path.resolve(__dirname, '../public');
const dbPath = path.resolve(__dirname, '../data/liga.db');

function hashPassword(password) {
  return crypto.createHash('sha256').update(password).digest('hex');
}

function getCookie(req, name) {
  const cookieHeader = req.headers.cookie || '';
  const cookie = cookieHeader
    .split(';')
    .map((part) => part.trim())
    .find((part) => part.startsWith(`${name}=`));

  return cookie ? decodeURIComponent(cookie.slice(name.length + 1)) : null;
}

export function createApp() {
  const app = express();
  const db = new DatabaseSync(dbPath);
  const sessions = new Map();

  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL UNIQUE,
      password TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS teams (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      name TEXT NOT NULL,
      city TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS players (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      team_id INTEGER NOT NULL,
      name TEXT NOT NULL,
      position TEXT NOT NULL,
      number INTEGER NOT NULL CHECK(number > 0),
      FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS matches (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      local_team_id INTEGER NOT NULL,
      visitor_team_id INTEGER NOT NULL,
      local_score INTEGER NOT NULL DEFAULT 0,
      visitor_score INTEGER NOT NULL DEFAULT 0,
      played_on TEXT NOT NULL,
      CHECK(local_team_id <> visitor_team_id),
      CHECK(local_score >= 0),
      CHECK(visitor_score >= 0),
      FOREIGN KEY (local_team_id) REFERENCES teams(id) ON DELETE CASCADE,
      FOREIGN KEY (visitor_team_id) REFERENCES teams(id) ON DELETE CASCADE
    );
  `);

  app.use(express.json({ limit: '1mb' }));
  app.use(express.urlencoded({ extended: true }));

  app.use((req, res, next) => {
    res.setHeader('Cache-Control', 'no-store');
    next();
  });

  function requireAuth(req, res, next) {
    const sessionId = getCookie(req, 'sessionId');
    const user = sessions.get(sessionId);

    if (!user) {
      return res.status(401).json({ message: 'Debes iniciar sesión para continuar.' });
    }

    req.user = user;
    next();
  }

  app.get('/api/session', (req, res) => {
    const sessionId = getCookie(req, 'sessionId');
    const user = sessions.get(sessionId);

    if (!user) {
      return res.json({ authenticated: false });
    }

    res.json({
      authenticated: true,
      user: { id: user.userId, username: user.username },
    });
  });

  app.post('/api/register', (req, res) => {
    const username = String(req.body?.username || '').trim();
    const password = String(req.body?.password || '');

    if (!username || !password) {
      return res.status(400).json({ message: 'Usuario y contraseña son obligatorios.' });
    }

    if (username.length < 3) {
      return res.status(400).json({ message: 'El nombre de usuario debe tener al menos 3 caracteres.' });
    }

    if (password.length < 4) {
      return res.status(400).json({ message: 'La contraseña debe tener al menos 4 caracteres.' });
    }

    const existing = db.prepare('SELECT id FROM users WHERE username = ?').get(username);
    if (existing) {
      return res.status(409).json({ message: 'El usuario ya existe.' });
    }

    const insert = db.prepare('INSERT INTO users (username, password) VALUES (?, ?)');
    const result = insert.run(username, hashPassword(password));
    const user = { userId: result.lastInsertRowid, username };
    const sessionId = crypto.randomUUID();
    sessions.set(sessionId, user);

    res.cookie('sessionId', sessionId, {
      httpOnly: true,
      sameSite: 'lax',
      maxAge: 1000 * 60 * 60 * 24,
    });

    res.status(201).json({ message: 'Usuario registrado correctamente.', user });
  });

  app.post('/api/login', (req, res) => {
    const username = String(req.body?.username || '').trim();
    const password = String(req.body?.password || '');

    if (!username || !password) {
      return res.status(400).json({ message: 'Usuario y contraseña son obligatorios.' });
    }

    const user = db.prepare('SELECT id, username, password FROM users WHERE username = ?').get(username);
    if (!user || user.password !== hashPassword(password)) {
      return res.status(401).json({ message: 'Credenciales inválidas.' });
    }

    const sessionId = crypto.randomUUID();
    const publicUser = { userId: user.id, username: user.username };
    sessions.set(sessionId, publicUser);

    res.cookie('sessionId', sessionId, {
      httpOnly: true,
      sameSite: 'lax',
      maxAge: 1000 * 60 * 60 * 24,
    });

    res.json({ message: 'Sesión iniciada correctamente.', user: publicUser });
  });

  app.post('/api/logout', (req, res) => {
    const sessionId = getCookie(req, 'sessionId');
    if (sessionId) {
      sessions.delete(sessionId);
    }

    res.clearCookie('sessionId');
    res.json({ message: 'Sesión cerrada.' });
  });

  app.get('/api/teams', requireAuth, (req, res) => {
    const teams = db.prepare('SELECT * FROM teams ORDER BY name COLLATE NOCASE ASC').all();
    res.json(teams);
  });

  app.post('/api/teams', requireAuth, (req, res) => {
    const name = String(req.body?.name || '').trim();
    const city = String(req.body?.city || '').trim();

    if (!name || !city) {
      return res.status(400).json({ message: 'El nombre y la ciudad del equipo son obligatorios.' });
    }

    const result = db.prepare('INSERT INTO teams (name, city) VALUES (?, ?)').run(name, city);
    const team = db.prepare('SELECT * FROM teams WHERE id = ?').get(result.lastInsertRowid);
    res.status(201).json(team);
  });

  app.put('/api/teams/:id', requireAuth, (req, res) => {
    const id = Number(req.params.id);
    const name = String(req.body?.name || '').trim();
    const city = String(req.body?.city || '').trim();

    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Identificador del equipo inválido.' });
    }

    if (!name || !city) {
      return res.status(400).json({ message: 'El nombre y la ciudad del equipo son obligatorios.' });
    }

    const existing = db.prepare('SELECT id FROM teams WHERE id = ?').get(id);
    if (!existing) {
      return res.status(404).json({ message: 'Equipo no encontrado.' });
    }

    db.prepare('UPDATE teams SET name = ?, city = ? WHERE id = ?').run(name, city, id);
    const team = db.prepare('SELECT * FROM teams WHERE id = ?').get(id);
    res.json(team);
  });

  app.delete('/api/teams/:id', requireAuth, (req, res) => {
    const id = Number(req.params.id);
    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Identificador del equipo inválido.' });
    }

    const existing = db.prepare('SELECT id FROM teams WHERE id = ?').get(id);
    if (!existing) {
      return res.status(404).json({ message: 'Equipo no encontrado.' });
    }

    db.prepare('DELETE FROM teams WHERE id = ?').run(id);
    res.json({ message: 'Equipo eliminado correctamente.' });
  });

  app.get('/api/teams/:id/players', requireAuth, (req, res) => {
    const teamId = Number(req.params.id);
    if (!Number.isInteger(teamId) || teamId <= 0) {
      return res.status(400).json({ message: 'Identificador de equipo inválido.' });
    }

    const players = db.prepare('SELECT * FROM players WHERE team_id = ? ORDER BY number ASC, name COLLATE NOCASE ASC').all(teamId);
    res.json(players);
  });

  app.post('/api/players', requireAuth, (req, res) => {
    const teamId = Number(req.body?.teamId);
    const name = String(req.body?.name || '').trim();
    const position = String(req.body?.position || '').trim();
    const number = Number(req.body?.number);

    if (!Number.isInteger(teamId) || teamId <= 0) {
      return res.status(400).json({ message: 'Debe seleccionar un equipo válido.' });
    }

    if (!name || !position || !Number.isInteger(number) || number <= 0) {
      return res.status(400).json({ message: 'Nombre, posición y número del jugador son obligatorios.' });
    }

    const teamExists = db.prepare('SELECT id FROM teams WHERE id = ?').get(teamId);
    if (!teamExists) {
      return res.status(404).json({ message: 'Equipo no encontrado.' });
    }

    const result = db.prepare('INSERT INTO players (team_id, name, position, number) VALUES (?, ?, ?, ?)').run(teamId, name, position, number);
    const player = db.prepare('SELECT * FROM players WHERE id = ?').get(result.lastInsertRowid);
    res.status(201).json(player);
  });

  app.put('/api/players/:id', requireAuth, (req, res) => {
    const id = Number(req.params.id);
    const teamId = Number(req.body?.teamId);
    const name = String(req.body?.name || '').trim();
    const position = String(req.body?.position || '').trim();
    const number = Number(req.body?.number);

    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Identificador del jugador inválido.' });
    }

    if (!Number.isInteger(teamId) || teamId <= 0) {
      return res.status(400).json({ message: 'Debe seleccionar un equipo válido.' });
    }

    if (!name || !position || !Number.isInteger(number) || number <= 0) {
      return res.status(400).json({ message: 'Nombre, posición y número del jugador son obligatorios.' });
    }

    const existing = db.prepare('SELECT id FROM players WHERE id = ?').get(id);
    if (!existing) {
      return res.status(404).json({ message: 'Jugador no encontrado.' });
    }

    const teamExists = db.prepare('SELECT id FROM teams WHERE id = ?').get(teamId);
    if (!teamExists) {
      return res.status(404).json({ message: 'Equipo no encontrado.' });
    }

    db.prepare('UPDATE players SET team_id = ?, name = ?, position = ?, number = ? WHERE id = ?').run(teamId, name, position, number, id);
    const player = db.prepare('SELECT * FROM players WHERE id = ?').get(id);
    res.json(player);
  });

  app.delete('/api/players/:id', requireAuth, (req, res) => {
    const id = Number(req.params.id);
    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Identificador del jugador inválido.' });
    }

    const existing = db.prepare('SELECT id FROM players WHERE id = ?').get(id);
    if (!existing) {
      return res.status(404).json({ message: 'Jugador no encontrado.' });
    }

    db.prepare('DELETE FROM players WHERE id = ?').run(id);
    res.json({ message: 'Jugador eliminado correctamente.' });
  });

  app.get('/api/matches', requireAuth, (req, res) => {
    const matches = db.prepare(`
      SELECT m.*, 
             lt.name AS local_name,
             lt.city AS local_city,
             vt.name AS visitor_name,
             vt.city AS visitor_city
      FROM matches m
      JOIN teams lt ON lt.id = m.local_team_id
      JOIN teams vt ON vt.id = m.visitor_team_id
      ORDER BY m.played_on DESC, m.id DESC
    `).all();
    res.json(matches);
  });

  app.post('/api/matches', requireAuth, (req, res) => {
    const localTeamId = Number(req.body?.localTeamId);
    const visitorTeamId = Number(req.body?.visitorTeamId);
    const localScore = Number(req.body?.localScore);
    const visitorScore = Number(req.body?.visitorScore);
    const playedOn = String(req.body?.playedOn || '').trim();

    if (!Number.isInteger(localTeamId) || !Number.isInteger(visitorTeamId) || localTeamId === visitorTeamId) {
      return res.status(400).json({ message: 'Debe seleccionar dos equipos distintos.' });
    }

    if (!Number.isInteger(localScore) || !Number.isInteger(visitorScore) || localScore < 0 || visitorScore < 0) {
      return res.status(400).json({ message: 'Los marcadores deben ser números válidos y no negativos.' });
    }

    if (!playedOn) {
      return res.status(400).json({ message: 'La fecha del partido es obligatoria.' });
    }

    const localExists = db.prepare('SELECT id FROM teams WHERE id = ?').get(localTeamId);
    const visitorExists = db.prepare('SELECT id FROM teams WHERE id = ?').get(visitorTeamId);
    if (!localExists || !visitorExists) {
      return res.status(404).json({ message: 'Uno de los equipos no existe.' });
    }

    const result = db.prepare('INSERT INTO matches (local_team_id, visitor_team_id, local_score, visitor_score, played_on) VALUES (?, ?, ?, ?, ?)')
      .run(localTeamId, visitorTeamId, localScore, visitorScore, playedOn);

    const match = db.prepare(`
      SELECT m.*, 
             lt.name AS local_name,
             lt.city AS local_city,
             vt.name AS visitor_name,
             vt.city AS visitor_city
      FROM matches m
      JOIN teams lt ON lt.id = m.local_team_id
      JOIN teams vt ON vt.id = m.visitor_team_id
      WHERE m.id = ?
    `).get(result.lastInsertRowid);

    res.status(201).json(match);
  });

  app.delete('/api/matches/:id', requireAuth, (req, res) => {
    const id = Number(req.params.id);
    if (!Number.isInteger(id) || id <= 0) {
      return res.status(400).json({ message: 'Identificador del partido inválido.' });
    }

    const existing = db.prepare('SELECT id FROM matches WHERE id = ?').get(id);
    if (!existing) {
      return res.status(404).json({ message: 'Partido no encontrado.' });
    }

    db.prepare('DELETE FROM matches WHERE id = ?').run(id);
    res.json({ message: 'Partido eliminado correctamente.' });
  });

  app.get('/api/stats', requireAuth, (req, res) => {
    const teams = db.prepare('SELECT * FROM teams ORDER BY name COLLATE NOCASE ASC').all();

    const standings = teams.map((team) => {
      const matches = db.prepare(`
        SELECT local_team_id AS team_id, local_score AS score_for, visitor_score AS score_against
        FROM matches WHERE local_team_id = ?
        UNION ALL
        SELECT visitor_team_id AS team_id, visitor_score AS score_for, local_score AS score_against
        FROM matches WHERE visitor_team_id = ?
      `).all(team.id, team.id);

      const games = matches.length;
      const wins = matches.filter((match) => {
        const isLocal = match.team_id === team.id && match.score_for > match.score_against;
        const isVisitor = match.team_id === team.id && match.score_for > match.score_against;
        return isLocal || isVisitor;
      }).length;

      const losses = games - wins;
      const pointsFor = matches.reduce((total, match) => total + match.score_for, 0);
      const pointsAgainst = matches.reduce((total, match) => total + match.score_against, 0);

      return {
        id: team.id,
        name: team.name,
        city: team.city,
        games,
        wins,
        losses,
        pointsFor,
        pointsAgainst,
        pointDifference: pointsFor - pointsAgainst,
      };
    });

    const sortedStandings = standings
      .map((entry) => ({
        ...entry,
        pct: entry.games === 0 ? 0 : entry.wins / entry.games,
      }))
      .sort((a, b) => b.wins - a.wins || b.pointDifference - a.pointDifference || b.pointsFor - a.pointsFor || a.name.localeCompare(b.name));

    const leaders = db.prepare(`
      SELECT p.id, p.name, p.position, p.number, p.team_id, t.name AS team_name, COUNT(m.id) AS games,
             SUM(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END) AS placeholder
      FROM players p
      JOIN teams t ON t.id = p.team_id
      LEFT JOIN matches m ON m.local_team_id = p.team_id OR m.visitor_team_id = p.team_id
      GROUP BY p.id, p.name, p.position, p.number, p.team_id, t.name
      ORDER BY p.name ASC
      LIMIT 10
    `).all();

    const recentMatches = db.prepare(`
      SELECT m.*, 
             lt.name AS local_name,
             lt.city AS local_city,
             vt.name AS visitor_name,
             vt.city AS visitor_city
      FROM matches m
      JOIN teams lt ON lt.id = m.local_team_id
      JOIN teams vt ON vt.id = m.visitor_team_id
      ORDER BY m.played_on DESC, m.id DESC
      LIMIT 5
    `).all();

    res.json({ standings: sortedStandings, leaders, recentMatches });
  });

  app.use(express.static(publicDir));

  app.get(/^(?!\/api).*/, (req, res) => {
    res.sendFile(path.join(publicDir, 'index.html'));
  });

  return app;
}

export default createApp;
