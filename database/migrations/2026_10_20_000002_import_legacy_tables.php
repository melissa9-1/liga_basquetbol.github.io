<?php

return static function (PDO $pdo): void {
    $imports = [
        'usuarios' => "INSERT INTO users (id, username, password)
            SELECT old.id, old.usuario, old.password FROM usuarios old
            WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.id = old.id OR u.username = old.usuario)
            ORDER BY old.id",
        'equipos' => "INSERT INTO teams (id, name, city)
            SELECT old.id, old.nombre, old.ciudad FROM equipos old
            WHERE NOT EXISTS (SELECT 1 FROM teams t WHERE t.id = old.id OR (t.name = old.nombre AND t.city = old.ciudad))
            ORDER BY old.id",
        'jugadores' => "INSERT INTO players (id, team_id, name, position, number)
            SELECT old.id, old.equipo_id, old.nombre, old.posicion, old.numero FROM jugadores old
            WHERE NOT EXISTS (
                SELECT 1 FROM players p WHERE p.id = old.id OR
                    (p.team_id = old.equipo_id AND p.name = old.nombre AND p.position = old.posicion AND p.number = old.numero)
            )
            ORDER BY old.id",
        'partidos' => "INSERT INTO matches (id, local_team_id, visitor_team_id, local_score, visitor_score, played_on)
            SELECT old.id, old.equipo_local_id, old.equipo_visitante_id, old.marcador_local, old.marcador_visitante, old.fecha
            FROM partidos old
            WHERE NOT EXISTS (
                SELECT 1 FROM matches m WHERE m.id = old.id OR
                    (m.local_team_id = old.equipo_local_id AND m.visitor_team_id = old.equipo_visitante_id
                     AND m.local_score = old.marcador_local AND m.visitor_score = old.marcador_visitante AND m.played_on = old.fecha)
            )
            ORDER BY old.id",
    ];

    foreach ($imports as $table => $sql) {
        $exists = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table'
        );
        $exists->execute(['table' => $table]);
        if ((int) $exists->fetchColumn() > 0) {
            $pdo->exec($sql);
        }
    }
};
