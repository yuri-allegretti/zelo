<?php
session_start();
require_once 'conexao.php';
header('Content-Type: application/json');

$user_id   = (int) $_SESSION['user_id'];
$ticket_id = (int) ($_REQUEST['ticket_id'] ?? 0);

if (!$user_id || !$ticket_id) { echo json_encode([]); exit; }

// Verificar acesso ao ticket
$stmt = $conn->prepare("
    SELECT t.user_id, t.agente_id, t.status, c.nivel 
    FROM tickets t
    JOIN cadastro c ON c.id = ?
    WHERE t.id = ?
");
$stmt->bind_param("ii", $user_id, $ticket_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
$stmt->close();

$tem_acesso = $info && (
    $info['user_id']   == $user_id ||
    $info['agente_id'] == $user_id ||
    in_array($info['nivel'], ['suporte', 'admin'])
);

if (!$tem_acesso) { echo json_encode([]); exit; }

// enviar mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = trim($_POST['mensagem'] ?? '');
    if (!$mensagem || $info['status'] === 'fechada') { echo json_encode([]); exit; }

    // Atribuir agente automaticamente na primeira mensagem
    if (!$info['agente_id'] && in_array($info['nivel'], ['suporte', 'admin'])) {
        $stmt = $conn->prepare("UPDATE tickets SET agente_id = ?, status = 'em_andamento' WHERE id = ?");
        $stmt->bind_param("ii", $user_id, $ticket_id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO mensagens (ticket_id, user_id, mensagem) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $ticket_id, $user_id, $mensagem);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true]);
    exit;
}


$ultimo_id = (int) ($_GET['ultimo_id'] ?? 0);

$stmt = $conn->prepare("
    SELECT m.id, m.user_id, m.mensagem,
           DATE_FORMAT(m.enviado_em, '%H:%i') AS enviado_em,
           c.nome
    FROM mensagens m
    JOIN cadastro c ON c.id = m.user_id
    WHERE m.ticket_id = ? AND m.id > ?
    ORDER BY m.id ASC
");
$stmt->bind_param("ii", $ticket_id, $ultimo_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($rows);