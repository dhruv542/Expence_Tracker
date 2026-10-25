<?php

final class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $statement = $this->pdo->prepare(
            'SELECT data FROM sessions WHERE id = ? AND last_activity >= ?'
        );
        $statement->execute([$id, time() - (int)ini_get('session.gc_maxlifetime')]);
        $data = $statement->fetchColumn();

        return $data === false ? '' : (string)$data;
    }

    public function write(string $id, string $data): bool
    {
        $now = time();
        $statement = $this->pdo->prepare(
            'INSERT INTO sessions (id, data, last_activity) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE data = ?, last_activity = ?'
        );

        return $statement->execute([$id, $data, $now, $data, $now]);
    }

    public function destroy(string $id): bool
    {
        $statement = $this->pdo->prepare('DELETE FROM sessions WHERE id = ?');

        return $statement->execute([$id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $statement = $this->pdo->prepare('DELETE FROM sessions WHERE last_activity < ?');

        if (!$statement->execute([time() - $max_lifetime])) {
            return false;
        }

        return $statement->rowCount();
    }
}
