<?php
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function url(string $path = ''): string
{
    $base = getenv('APP_URL') ?: 'http://localhost:8000';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function ensureDatabase(): void
{
    $pdo = getPdo();
    $sql = file_get_contents(__DIR__ . '/../schema.sql');
    if ($sql === false) {
        return;
    }

    $pdo->exec($sql);
}

function getGoalStage(int $progress): string
{
    if ($progress < 25) {
        return 'Broto';
    }

    if ($progress < 50) {
        return 'Planta';
    }

    if ($progress < 75) {
        return 'Flor';
    }

    return 'Árvore';
}

function getStageEmoji(string $stage): string
{
    return match ($stage) {
        'Broto' => '🌱',
        'Planta' => '🌿',
        'Flor' => '🌸',
        'Árvore' => '🌳',
        default => '🌱',
    };
}

function getMotivationalMessage(): string
{
    $messages = [
        'Cada passo conta. Continue plantando hoje.',
        'Seu progresso é a semente do futuro.',
        'Você está mais perto do que imagina.',
        'A consistência vence a perfeição.',
        'Transforme esforço em crescimento.',
        'Mantenha o foco em uma pequena vitória por vez.',
    ];

    return $messages[(int) (date('z')) % count($messages)];
}

function registerUser(string $name, string $email, string $password): array
{
    $name = trim($name);
    $email = strtolower(trim($email));

    if ($name === '' || $email === '' || $password === '') {
        return ['ok' => false, 'message' => 'Preencha todos os campos.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Email inválido.'];
    }

    if (strlen($password) < 6) {
        return ['ok' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres.'];
    }

    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        return ['ok' => false, 'message' => 'Este email já está cadastrado.'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $passwordHash,
    ]);

    return ['ok' => true, 'message' => 'Cadastro realizado com sucesso!'];
}

function loginUserByCredentials(string $email, string $password): array
{
    $email = strtolower(trim($email));

    if ($email === '' || $password === '') {
        return ['ok' => false, 'message' => 'Email e senha são obrigatórios.'];
    }

    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'message' => 'Email ou senha incorretos.'];
    }

    loginUser((int) $user['id'], $user['name']);
    return ['ok' => true, 'message' => 'Login realizado com sucesso!'];
}

function createGoalForUser(int $userId, string $title, string $category): array
{
    $title = trim($title);
    $category = trim($category);

    if ($title === '' || $category === '') {
        return ['ok' => false, 'message' => 'Título e categoria são obrigatórios.'];
    }

    $pdo = getPdo();
    $stmt = $pdo->prepare('INSERT INTO goals (user_id, title, category, progress) VALUES (:user_id, :title, :category, 0)');
    $stmt->execute([
        'user_id' => $userId,
        'title' => $title,
        'category' => $category,
    ]);

    return ['ok' => true, 'message' => 'Objetivo plantado com sucesso!'];
}

function addProgressToGoal(int $goalId, int $amount, ?string $note = null): array
{
    if ($amount <= 0) {
        return ['ok' => false, 'message' => 'A quantidade de progresso deve ser maior que zero.'];
    }

    $pdo = getPdo();

    $stmt = $pdo->prepare('SELECT * FROM goals WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $goalId]);
    $goal = $stmt->fetch();

    if (!$goal) {
        return ['ok' => false, 'message' => 'Objetivo não encontrado.'];
    }

    $newProgress = min(100, (int) $goal['progress'] + $amount);

    $update = $pdo->prepare('UPDATE goals SET progress = :progress, updated_at = NOW() WHERE id = :id');
    $update->execute(['progress' => $newProgress, 'id' => $goalId]);

    $insert = $pdo->prepare('INSERT INTO goal_progress (goal_id, amount, note) VALUES (:goal_id, :amount, :note)');
    $insert->execute([
        'goal_id' => $goalId,
        'amount' => $amount,
        'note' => $note ? trim($note) : null,
    ]);

    return ['ok' => true, 'message' => 'Progresso registrado com sucesso!'];
}

function getUserGoals(int $userId): array
{
    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT * FROM goals WHERE user_id = :user_id ORDER BY updated_at DESC');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function getGoalById(int $goalId, int $userId): ?array
{
    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT * FROM goals WHERE id = :id AND user_id = :user_id LIMIT 1');
    $stmt->execute(['id' => $goalId, 'user_id' => $userId]);
    $goal = $stmt->fetch();
    return $goal ?: null;
}

function getGoalProgressEntries(int $goalId): array
{
    $pdo = getPdo();
    $stmt = $pdo->prepare('SELECT * FROM goal_progress WHERE goal_id = :goal_id ORDER BY created_at DESC');
    $stmt->execute(['goal_id' => $goalId]);
    return $stmt->fetchAll();
}

function getGoalSummary(int $userId): array
{
    $goals = getUserGoals($userId);
    $total = count($goals);
    $completed = 0;
    $average = 0;

    foreach ($goals as $goal) {
        $average += (int) $goal['progress'];
        if ((int) $goal['progress'] >= 100) {
            $completed++;
        }
    }

    if ($total > 0) {
        $average = (int) round($average / $total);
    }

    return ['total' => $total, 'completed' => $completed, 'average' => $average];
}

function e(string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
