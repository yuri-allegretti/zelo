<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php'; 


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);


$email  = isset($dados['email'])  ? trim($dados['email'])  : '';//ap
$senha = isset($dados['senha'])   ? $dados['senha']        : '';


if (empty($email) || empty($senha)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}


$stmt = $pdo->prepare('SELECT id, nome, email, senha, nivel FROM cadastro WHERE email = ?');//ap
$stmt->execute([$email]);
$usuario = $stmt->fetch(); 


if (!$usuario || !password_verify($senha, $usuario['senha'])) {//ap verif =
    echo json_encode(['sucesso' => false, 'mensagem' => 'Email ou senha incorretos.']);
    exit;
}


session_start();
$_SESSION['user_id'] = $usuario['id'];
$_SESSION['user_nome'] = $usuario['nome'];//ap
$_SESSION['user_email'] = $usuario['email'];


$stmt = $pdo->prepare('SELECT account_id FROM cadastro WHERE id = ?');
$stmt->execute([$usuario['id']]);
$dadosConta = $stmt->fetch();

$account_id = $dadosConta['account_id'] ?? null;


if ($account_id) {
    $user_id = (int)$usuario['id'];
    
    $projectRoot = dirname(__DIR__, 3);
    $saldoScript = $projectRoot . DIRECTORY_SEPARATOR . 'saldo.py';
    $transactionsScript = $projectRoot . DIRECTORY_SEPARATOR . 'transactions.py';

    $pythonBin = 'python';
    $pythonCheck = trim((string)shell_exec('where python 2>NUL'));//
    if ($pythonCheck === '') {
        $pyCheck = trim((string)shell_exec('where py 2>NUL'));
        if ($pyCheck !== '') {
            $pythonBin = 'py';
        }
    }

   
    $saldoCmd = sprintf(
        '%s %s %s 2>&1',
        escapeshellcmd($pythonBin),
        escapeshellarg($saldoScript),
        escapeshellarg((string)$user_id)
    );

    
    $saldoSaida = trim((string)shell_exec($saldoCmd)); 
    if ($saldoSaida !== '' && is_numeric($saldoSaida)) {
        $stmtSaldo = $pdo->prepare('UPDATE cadastro SET saldo = ? WHERE id = ?');//salva retorno no db
        $stmtSaldo->execute([(float)$saldoSaida, $user_id]);
    }

  
    $cmd = sprintf(
        '%s %s %s %s 2>&1',
        escapeshellcmd($pythonBin),
        escapeshellarg($transactionsScript),
        escapeshellarg((string)$user_id),
        escapeshellarg((string)$account_id)
    );

    $logFile = $projectRoot . DIRECTORY_SEPARATOR . 'transactions_login.log';

    
    $bgCmd = 'start "" /B cmd /C "' . $cmd . ' >> ' . escapeshellarg($logFile) . ' 2>&1"';
    exec($bgCmd);

}

echo json_encode([
    'sucesso'  => true,
    'mensagem' => 'Login realizado com sucesso!',
    'usuario'  => ['id' => $usuario['id'],'email' => $usuario['email'], 'nome' => $usuario['nome'], 'nivel' => $usuario['nivel']]//ap
]);
