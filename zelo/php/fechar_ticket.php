<?php
session_start();
require_once 'conexao.php';

$user_id   = (int) $_SESSION['user_id'];
$ticket_id = (int) ($_GET['ticket_id'] ?? 0);

$stmt = $conn->prepare("SELECT nivel FROM cadastro WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$me || !in_array($me['nivel'], ['suporte', 'admin'])) {
    header('Location: ../login/html/login.html');
    exit;
}

$stmt = $conn->prepare("
    UPDATE tickets SET status = 'fechada', data_fechamento = NOW() 
    WHERE id = ? AND (agente_id = ? OR ? = 'admin')
");
$stmt->bind_param("iis", $ticket_id, $user_id, $me['nivel']);
$stmt->execute();
$stmt->close();

header('Location: /zelo-main/Suporte/suporte.php');
exit;