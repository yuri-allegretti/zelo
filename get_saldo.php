<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['erro' => 'Usuário não logado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$python = trim((string) shell_exec("where python 2>NUL"));
$pythonCmd = "python";

if (empty($python)) {
    $pyLauncher = trim((string) shell_exec("where py 2>NUL"));
    if (!empty($pyLauncher)) {
        $pythonCmd = "py";
    }
}

$script = realpath(__DIR__ . '/../../saldo.py');
$saldo = '';
if ($script) {
    $cmd = $pythonCmd . " " . escapeshellarg($script) . " " . escapeshellarg((string) $user_id) . " 2>&1";
    $saldo = (string) shell_exec($cmd);
}

echo json_encode(['saldo' => trim($saldo)]);
?>