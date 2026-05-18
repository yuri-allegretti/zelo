<?php

$stmt = $conn->prepare("SELECT nivel FROM cadastro WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r || $r['nivel'] !== 'admin') {
    echo "<div class='page-container'><p>Acesso negado.</p></div>";
    return;
}


$filtro = $_GET['filtro'] ?? 'pendente';
$filtros_validos = ['pendente', 'aprovada', 'negada'];
if (!in_array($filtro, $filtros_validos)) $filtro = 'pendente';

$stmt = $conn->prepare("
    SELECT s.id, s.nome_completo, s.idade, s.motivacao, s.experiencia,
           s.status, s.data_solicitacao, c.nome AS nome_usuario, c.email
    FROM solicitacao_suporte s
    JOIN cadastro c ON c.id = s.user_id
    WHERE s.status = ?
    ORDER BY s.data_solicitacao DESC
");
$stmt->bind_param("s", $filtro);
$stmt->execute();
$solicitacoes = $stmt->get_result();
$stmt->close();
?>

<div class="page-container">
    <h1 class="titulo-pagina">Painel Admin — Solicitações de Suporte</h1>

    <div class="admin-filtros">
        <a href="?page=admin_suporte&filtro=pendente" class="btn-filtro <?= $filtro === 'pendente' ? 'ativo' : '' ?>">⏳ Pendentes</a>
        <a href="?page=admin_suporte&filtro=aprovada" class="btn-filtro <?= $filtro === 'aprovada' ? 'ativo' : '' ?>">✅ Aprovadas</a>
        <a href="?page=admin_suporte&filtro=negada"   class="btn-filtro <?= $filtro === 'negada'   ? 'ativo' : '' ?>">❌ Negadas</a>
    </div>

    <?php if ($solicitacoes->num_rows === 0): ?>
        <p class="sem-resultados">Nenhuma solicitação <?= $filtro ?>.</p>
    <?php endif; ?>

    <div class="admin-lista">
    <?php while ($s = $solicitacoes->fetch_assoc()): ?>
        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <strong><?= htmlspecialchars($s['nome_completo']) ?></strong>
                    <span class="admin-sub">usuário: <?= htmlspecialchars($s['nome_usuario']) ?> — <?= htmlspecialchars($s['email']) ?></span>
                </div>
                <span class="badge-status <?= $s['status'] ?>"><?= $s['status'] ?></span>
            </div>

            <div class="admin-card-body">
                <p><strong>Idade:</strong> <?= (int)$s['idade'] ?> anos</p>
                <p><strong>Motivação:</strong><br><?= nl2br(htmlspecialchars($s['motivacao'])) ?></p>
                <?php if ($s['experiencia']): ?>
                    <p><strong>Experiência:</strong><br><?= nl2br(htmlspecialchars($s['experiencia'])) ?></p>
                <?php endif; ?>
                <p class="admin-data">Enviado em: <?= $s['data_solicitacao'] ?></p>
            </div>

            <?php if ($s['status'] === 'pendente'): ?>
            <div class="admin-card-actions">
                <a href="?page=responder_suporte&id=<?= $s['id'] ?>&acao=aprovar" class="btn-acao aprovar">✅ Aprovar</a>
                <a href="?page=responder_suporte&id=<?= $s['id'] ?>&acao=negar"   class="btn-acao negar">❌ Negar</a>
            </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
    </div>
</div>