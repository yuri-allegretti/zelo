<?php
session_start();
require_once '../zelo/php/conexao.php';


$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT nivel, nome FROM cadastro WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$me || !in_array($me['nivel'], ['suporte', 'admin'])) {
    header('Location: ../zelo/login/html/login.html');
    exit;
}


$filtro = $_GET['filtro'] ?? 'aberta';
$filtros_validos = ['aberta', 'em_andamento', 'fechada'];
if (!in_array($filtro, $filtros_validos)) $filtro = 'aberta';

$stmt = $conn->prepare("
    SELECT t.id, t.titulo, t.status, t.prioridade, t.categoria,
           DATE_FORMAT(t.data_abertura, '%d/%m/%Y') AS data_ab,
           DATE_FORMAT(t.data_abertura, '%H:%i')    AS hora_ab,
           c.nome AS nome_usuario
    FROM tickets t
    JOIN cadastro c ON c.id = t.user_id
    WHERE t.status = ?
    ORDER BY 
        FIELD(t.prioridade, 'alta', 'media', 'baixa'),
        t.data_abertura ASC
");
$stmt->bind_param("s", $filtro);
$stmt->execute();
$tickets = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte Zelo</title>
    <link rel="stylesheet" href="suporte.css">
    <link rel="stylesheet" href="../zelo/php/style1.css">
</head>
<body>
    <header>
        <h1>Suporte Zelo</h1>
        <div style="display:flex; gap:1rem; align-items:center;">
            <span>Olá, <?= htmlspecialchars($me['nome']) ?></span>
            <a href="../zelo/login/html/login.html" class="sair">Sair</a>
        </div>
    </header>

    <div class="prin-chats">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Chamados</h2>
            <div class="admin-filtros">
                <a href="?filtro=aberta"        class="btn-filtro <?= $filtro==='aberta'       ?'ativo':'' ?>">🟡 Abertos</a>
                <a href="?filtro=em_andamento"  class="btn-filtro <?= $filtro==='em_andamento' ?'ativo':'' ?>">🔵 Em andamento</a>
                <a href="?filtro=fechada"        class="btn-filtro <?= $filtro==='fechada'      ?'ativo':'' ?>">⚫ Fechados</a>
            </div>
        </div>
        <hr>

        <div class="cards">
        <?php if ($tickets->num_rows === 0): ?>
            <p style="color:#666; padding:1rem;">Nenhum chamado <?= $filtro ?>.</p>
        <?php endif; ?>

        <?php while ($t = $tickets->fetch_assoc()): ?>
            <div class="card prioridade-<?= $t['prioridade'] ?>">
                <h3>Chat #<?= str_pad($t['id'], 3, '0', STR_PAD_LEFT) ?></h3>
                <hr>
                <h4>Usuário:</h4>
                <h5><?= htmlspecialchars($t['nome_usuario']) ?></h5>
                <h4>Aberto em:</h4>
                <h5><?= $t['data_ab'] ?></h5>
                <h4>Horário:</h4>
                <h5><?= $t['hora_ab'] ?></h5>
                <h4>Problema:</h4>
                <h5><?= htmlspecialchars($t['titulo']) ?></h5>
                <h4>Prioridade:</h4>
                <h5><?= $t['prioridade'] ?></h5>
                <div style="display:flex; gap:0.5rem; margin-top:0.75rem; flex-wrap:wrap;">
                    <a href="chat_agente.php?ticket_id=<?= $t['id'] ?>" class="btn-chat">Abrir Chat</a>
                    <?php if ($t['status'] !== 'fechada'): ?>
                        <a href="../zelo/php/fechar_ticket.php?ticket_id=<?= $t['id'] ?>" 
                           class="btn-fechar"
                           onclick="return confirm('Fechar este chamado?')">Fechar</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    </div>
</body>
</html>