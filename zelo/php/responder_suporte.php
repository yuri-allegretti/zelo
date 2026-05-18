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

$id   = (int) ($_GET['id'] ?? 0);
$acao = $_GET['acao'] ?? '';

if (!$id || !in_array($acao, ['aprovar', 'negar'])) {
    header('Location: index.php?page=admin_suporte');
    exit;
}


$stmt = $conn->prepare("
    SELECT s.*, c.nome AS nome_usuario 
    FROM solicitacao_suporte s 
    JOIN cadastro c ON c.id = s.user_id 
    WHERE s.id = ? AND s.status = 'pendente'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$s = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$s) {
    echo "<div class='page-container'><p>Solicitação não encontrada ou já respondida.</p><a href='?page=admin_suporte'>Voltar</a></div>";
    return;
}

$label = $acao === 'aprovar' ? '✅ Aprovar' : '❌ Negar';
$cor   = $acao === 'aprovar' ? 'aprovar' : 'negar';
?>

<div class="page-container">
    <a href="?page=admin_suporte" class="btn-voltar">← Voltar</a>
    <h1 class="titulo-pagina"><?= $label ?> candidatura</h1>

    <div class="admin-card" style="max-width:600px; margin-bottom:1.5rem;">
        <div class="admin-card-header">
            <strong><?= htmlspecialchars($s['nome_completo']) ?></strong>
            <span class="admin-sub">Usuário: <?= htmlspecialchars($s['nome_usuario']) ?></span>
        </div>
        <div class="admin-card-body">
            <p><strong>Motivação:</strong><br><?= nl2br(htmlspecialchars($s['motivacao'])) ?></p>
            <?php if ($s['experiencia']): ?>
                <p><strong>Experiência:</strong><br><?= nl2br(htmlspecialchars($s['experiencia'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <form action="?page=salvar_resposta_suporte" method="POST" class="form-suporte">
        <input type="hidden" name="id"   value="<?= $id ?>">
        <input type="hidden" name="acao" value="<?= $acao ?>">

        <div class="form-group">
            <label for="observacao">Observação para o usuário <span class="opcional">(opcional)</span></label>
            <textarea id="observacao" name="observacao" class="form-control" rows="4"
                placeholder="Ex: Bem-vindo ao time! / Infelizmente não atende os requisitos..."></textarea>
        </div>

        <button type="submit" class="btn-acao <?= $cor ?> btn-submit"><?= $label ?></button>
    </form>
</div>