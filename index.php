<?php
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';

startAppSession();
ensureDatabase();

$route = $_GET['page'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $result = registerUser($_POST['name'] ?? '', $_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['ok']) {
            setFlash($result['message']);
            redirect('/?page=login');
        }
        setFlash($result['message'], 'error');
        redirect('/?page=register');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $result = loginUserByCredentials($_POST['email'] ?? '', $_POST['password'] ?? '');
        if ($result['ok']) {
            setFlash('Bem-vindo(a) de volta!');
            redirect('/?page=dashboard');
        }
        setFlash($result['message'], 'error');
        redirect('/?page=login');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'logout') {
        logoutUser();
        setFlash('Você saiu da sua conta.');
        redirect('/?page=login');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'create_goal') {
        requireLogin();
        $result = createGoalForUser((int) $_SESSION['user_id'], $_POST['title'] ?? '', $_POST['category'] ?? '');
        if ($result['ok']) {
            setFlash($result['message']);
            redirect('/?page=dashboard');
        }
        setFlash($result['message'], 'error');
        redirect('/?page=dashboard');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_progress') {
        requireLogin();
        $goalId = (int) ($_POST['goal_id'] ?? 0);
        $amount = (int) ($_POST['amount'] ?? 0);
        $note = $_POST['note'] ?? null;
        $result = addProgressToGoal($goalId, $amount, $note);
        if ($result['ok']) {
            setFlash($result['message']);
            redirect('/?page=goal&id=' . $goalId);
        }
        setFlash($result['message'], 'error');
        redirect('/?page=goal&id=' . $goalId);
    }
}

if (!isLoggedIn() && !in_array($route, ['login', 'register'], true)) {
    $route = 'login';
}

if (isLoggedIn() && $route === 'login') {
    $route = 'dashboard';
}

function renderLayout(string $title, string $content): void
{
    $flash = getFlash();
    $current = currentUser();
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> | LifeSeed</title>
        <link rel="stylesheet" href="/style.css">
    </head>
    <body class="theme-default">
        <div class="page-shell">
            <header class="topbar">
                <div class="brand-wrap">
                    <div class="logo">🌱</div>
                    <div>
                        <h1>LifeSeed</h1>
                    </div>
                </div>

                <?php if ($current): ?>
                    <nav class="nav-links">
                        <a href="/?page=dashboard">Dashboard</a>
                        <form method="post" action="/?page=dashboard">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="text-button">Sair</button>
                        </form>
                    </nav>
                <?php endif; ?>
            </header>

            <?php if ($flash): ?>
                <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <main class="content">
                <?= $content ?>
            </main>
        </div>

        <script src="/app.js"></script>
    </body>
    </html>
    <?php
}

switch ($route) {
    case 'login':
        ob_start();
        ?>
        <section class="auth-card">
            <div class="auth-copy">
                <span class="label">Seu jardim pessoal</span>
                <h2>Plante metas, veja ideias crescerem.</h2>
                <p>Registre o que você quer alcançar e acompanhe a evolução como uma planta viva.</p>
            </div>

            <form method="post" class="auth-form">
                <input type="hidden" name="action" value="login">
                <h3>Entrar</h3>
                <label>
                    Email
                    <input type="email" name="email" required>
                </label>
                <label>
                    Senha
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="primary-btn">Entrar</button>
                <p class="auth-alt">Ainda não tem conta? <a href="/?page=register">Cadastre-se</a></p>
            </form>
        </section>
        <?php
        renderLayout('Entrar', ob_get_clean());
        break;

    case 'register':
        ob_start();
        ?>
        <section class="auth-card">
            <div class="auth-copy">
                <span class="label">Novo jardim</span>
                <h2>Comece a plantar seus objetivos.</h2>
                <p>Cadastre-se e transforme cada meta em uma planta que cresce conforme você avança.</p>
            </div>

            <form method="post" class="auth-form">
                <input type="hidden" name="action" value="register">
                <h3>Criar conta</h3>
                <label>
                    Nome
                    <input type="text" name="name" required>
                </label>
                <label>
                    Email
                    <input type="email" name="email" required>
                </label>
                <label>
                    Senha
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="primary-btn">Cadastrar</button>
                <p class="auth-alt">Já tem conta? <a href="/?page=login">Entrar</a></p>
            </form>
        </section>
        <?php
        renderLayout('Cadastro', ob_get_clean());
        break;

    case 'dashboard':
        requireLogin();
        $user = currentUser();
        $goals = getUserGoals((int) $user['id']);
        $summary = getGoalSummary((int) $user['id']);
        ob_start();
        ?>
        <section class="dashboard">
            <div class="welcome-row">
                <div>
                    <span class="label">Painel</span>
                    <h2>Olá, <?= e($user['name']) ?> 👋</h2>
                </div>
                <a href="/?page=dashboard&action=new-goal" class="primary-btn">+ Plantar nova semente</a>
            </div>

            <div class="stats-grid">
                <article class="stat-card">
                    <span>Objetivos</span>
                    <strong><?= e((string) $summary['total']) ?></strong>
                </article>
                <article class="stat-card">
                    <span>Concluídos</span>
                    <strong><?= e((string) $summary['completed']) ?></strong>
                </article>
                <article class="stat-card">
                    <span>Média</span>
                    <strong><?= e((string) $summary['average']) ?>%</strong>
                </article>
            </div>

            <div class="motivation-box">
                <strong>Frase motivacional do dia</strong>
                <p><?= e(getMotivationalMessage()) ?></p>
            </div>

            <?php if (isset($_GET['action']) && $_GET['action'] === 'new-goal'): ?>
                <form method="post" class="goal-form">
                    <input type="hidden" name="action" value="create_goal">
                    <h3>Plantar uma semente</h3>
                    <label>
                        Objetivo
                        <input type="text" name="title" placeholder="Ex.: aprender inglês" required>
                    </label>
                    <label>
                        Categoria
                        <select name="category" required>
                            <option value="Estudos">Estudos</option>
                            <option value="Financeiro">Financeiro</option>
                            <option value="Saúde">Saúde</option>
                            <option value="Trabalho">Trabalho</option>
                            <option value="Pessoal">Pessoal</option>
                        </select>
                    </label>
                    <button type="submit" class="primary-btn">Salvar objetivo</button>
                </form>
            <?php endif; ?>

            <div class="goals-grid">
                <?php if (empty($goals)): ?>
                    <div class="empty-card">
                        <h3>Seu jardim está vazio ainda 🌱</h3>
                        <p>Crie seu primeiro objetivo e comece a crescer.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($goals as $goal): ?>
                        <?php $stage = getGoalStage((int) $goal['progress']); ?>
                        <article class="goal-card">
                            <div class="goal-head">
                                <div class="goal-icon"><?= e(getStageEmoji($stage)) ?></div>
                                <div>
                                    <span class="goal-category"><?= e($goal['category']) ?></span>
                                    <h3><?= e($goal['title']) ?></h3>
                                </div>
                            </div>

                            <div class="stage-badge">
                                <span><?= e($stage) ?></span>
                            </div>

                            <div class="progress-wrap">
                                <div class="progress-bar">
                                    <span style="width: <?= e((string) (int) $goal['progress']) ?>%"></span>
                                </div>
                                <strong><?= e((string) (int) $goal['progress']) ?>%</strong>
                            </div>

                            <div class="goal-actions">
                                <a href="/?page=goal&id=<?= e((string) $goal['id']) ?>" class="secondary-btn">Abrir</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <?php
        renderLayout('Dashboard', ob_get_clean());
        break;

    case 'goal':
        requireLogin();
        $user = currentUser();
        $goalId = (int) ($_GET['id'] ?? 0);
        $goal = getGoalById($goalId, (int) $user['id']);

        if (!$goal) {
            redirect('/?page=dashboard');
        }

        $stage = getGoalStage((int) $goal['progress']);
        $progressEntries = getGoalProgressEntries($goalId);
        ob_start();
        ?>
        <section class="goal-detail">
            <div class="detail-header">
                <div>
                    <span class="label"><?= e($goal['category']) ?></span>
                    <h2><?= e($goal['title']) ?></h2>
                </div>
                <div class="large-stage"><?= e(getStageEmoji($stage)) ?> <?= e($stage) ?></div>
            </div>

            <div class="progress-panel">
                <div class="progress-wrap large">
                    <div class="progress-bar">
                        <span style="width: <?= e((string) (int) $goal['progress']) ?>%"></span>
                    </div>
                    <strong><?= e((string) (int) $goal['progress']) ?>%</strong>
                </div>
            </div>

            <form method="post" class="progress-form">
                <input type="hidden" name="action" value="add_progress">
                <input type="hidden" name="goal_id" value="<?= e((string) $goal['id']) ?>">
                <label>
                    Progresso (+%)
                    <input type="number" min="1" max="100" name="amount" required>
                </label>
                <label>
                    Observação
                    <input type="text" name="note" placeholder="Ex.: revisei a última lição">
                </label>
                <button type="submit" class="primary-btn">Registrar progresso</button>
            </form>

            <div class="history-card">
                <h3>Histórico de progresso</h3>
                <?php if (empty($progressEntries)): ?>
                    <p>Ainda não há registros para este objetivo.</p>
                <?php else: ?>
                    <ul class="progress-list">
                        <?php foreach ($progressEntries as $entry): ?>
                            <li>
                                <div>
                                    <strong>+<?= e((string) (int) $entry['amount']) ?>%</strong>
                                    <span><?= e($entry['created_at']) ?></span>
                                </div>
                                <?php if (!empty($entry['note'])): ?>
                                    <p><?= e($entry['note']) ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </section>
        <?php
        renderLayout('Objetivo', ob_get_clean());
        break;

    default:
        redirect('/?page=dashboard');
}
