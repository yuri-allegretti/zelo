<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=admin_suporte');
    exit;
}


$stmt = $conn->prepare("SELECT nivel FROM cadastro WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$r || $r['nivel'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$admin_id    = (int) $_SESSION['user_id'];
$id          = (int) ($_POST['id'] ?? 0);
$acao        = $_POST['acao'] ?? '';
$observacao  = trim($_POST['observacao'] ?? '') ?: null;

if (!$id || !in_array($acao, ['aprovar', 'negar'])) {
    header('Location: index.php?page=admin_suporte');
    exit;
}

$novo_status = $acao === 'aprovar' ? 'aprovada' : 'negada';


$stmt = $conn->prepare("SELECT user_id FROM solicitacao_suporte WHERE id = ? AND status = 'pendente'");
$stmt->bind_param("i", $id);
$stmt->execute();
$sol = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$sol) {
    header('Location: index.php?page=admin_suporte');
    exit;
}


$stmt = $conn->prepare("
    UPDATE solicitacao_suporte 
    SET status = ?, admin_id = ?, observacao_admin = ?, data_resposta = NOW()
    WHERE id = ?
");
$stmt->bind_param("sisi", $novo_status, $admin_id, $observacao, $id);
$stmt->execute();
$stmt->close();

if ($acao === 'aprovar') {
    $user_id = (int) $sol['user_id'];
    $stmt = $conn->prepare("UPDATE cadastro SET nivel = 'suporte' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

header('Location: index.php?page=admin_suporte&msg=ok');
exit;