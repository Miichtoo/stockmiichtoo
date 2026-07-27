<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/config.php';

function jsonResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        jsonResponse(['ok' => false, 'error' => 'JSON invalide.'], 400);
    }

    return $data;
}

function cleanName(mixed $value): string
{
    $name = trim((string) $value);
    if ($name === '' || mb_strlen($name) > 100) {
        jsonResponse(['ok' => false, 'error' => 'Nom invalide (1 à 100 caractères).'], 422);
    }
    return $name;
}

try {
    $pdo = db();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $action = $_GET['action'] ?? 'list';

    if ($method === 'GET' && $action === 'list') {
        $search = trim((string) ($_GET['q'] ?? ''));

        if ($search === '') {
            $stmt = $pdo->query('SELECT id, name, quantity, icon, created_at, updated_at FROM resources ORDER BY name ASC');
        } else {
            $stmt = $pdo->prepare('SELECT id, name, quantity, icon, created_at, updated_at FROM resources WHERE name LIKE :search ORDER BY name ASC');
            $stmt->execute(['search' => '%' . $search . '%']);
        }

        jsonResponse(['ok' => true, 'resources' => $stmt->fetchAll()]);
    }

    $data = body();

    if ($method === 'POST' && $action === 'create') {
        $name = cleanName($data['name'] ?? '');
        $quantity = max(0, (int) ($data['quantity'] ?? 0));
        $icon = trim((string) ($data['icon'] ?? '📦')) ?: '📦';
        $icon = mb_substr($icon, 0, 16);

        $stmt = $pdo->prepare('INSERT INTO resources (name, quantity, icon) VALUES (:name, :quantity, :icon)');
        $stmt->execute([
            'name' => $name,
            'quantity' => $quantity,
            'icon' => $icon,
        ]);

        jsonResponse(['ok' => true, 'id' => (int) $pdo->lastInsertId()], 201);
    }

    if ($method === 'POST' && $action === 'change') {
        $id = (int) ($data['id'] ?? 0);
        $delta = (int) ($data['delta'] ?? 0);

        if ($id < 1 || $delta === 0 || abs($delta) > 1000000) {
            jsonResponse(['ok' => false, 'error' => 'Modification invalide.'], 422);
        }

        $stmt = $pdo->prepare('UPDATE resources SET quantity = GREATEST(0, quantity + :delta) WHERE id = :id');
        $stmt->execute(['delta' => $delta, 'id' => $id]);

        if ($stmt->rowCount() === 0) {
            $check = $pdo->prepare('SELECT id FROM resources WHERE id = :id');
            $check->execute(['id' => $id]);
            if (!$check->fetch()) {
                jsonResponse(['ok' => false, 'error' => 'Ressource introuvable.'], 404);
            }
        }

        $stmt = $pdo->prepare('SELECT id, name, quantity, icon FROM resources WHERE id = :id');
        $stmt->execute(['id' => $id]);
        jsonResponse(['ok' => true, 'resource' => $stmt->fetch()]);
    }

    if ($method === 'POST' && $action === 'update') {
        $id = (int) ($data['id'] ?? 0);
        $name = cleanName($data['name'] ?? '');
        $quantity = max(0, (int) ($data['quantity'] ?? 0));
        $icon = trim((string) ($data['icon'] ?? '📦')) ?: '📦';
        $icon = mb_substr($icon, 0, 16);

        if ($id < 1) {
            jsonResponse(['ok' => false, 'error' => 'Identifiant invalide.'], 422);
        }

        $stmt = $pdo->prepare('UPDATE resources SET name = :name, quantity = :quantity, icon = :icon WHERE id = :id');
        $stmt->execute([
            'name' => $name,
            'quantity' => $quantity,
            'icon' => $icon,
            'id' => $id,
        ]);

        jsonResponse(['ok' => true]);
    }

    if ($method === 'POST' && $action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) {
            jsonResponse(['ok' => false, 'error' => 'Identifiant invalide.'], 422);
        }

        $stmt = $pdo->prepare('DELETE FROM resources WHERE id = :id');
        $stmt->execute(['id' => $id]);
        jsonResponse(['ok' => true]);
    }

    jsonResponse(['ok' => false, 'error' => 'Route introuvable.'], 404);
} catch (PDOException $e) {
    $isDuplicate = (int) ($e->errorInfo[1] ?? 0) === 1062;
    jsonResponse([
        'ok' => false,
        'error' => $isDuplicate ? 'Une ressource avec ce nom existe déjà.' : 'Erreur de base de données.',
    ], $isDuplicate ? 409 : 500);
} catch (Throwable $e) {
    jsonResponse(['ok' => false, 'error' => 'Erreur serveur.'], 500);
}
