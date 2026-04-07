<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cadastro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      body {
        background-color: #f8f9fa; 
      }
      .navbar {
        border-bottom: 1px solid #dee2e6;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07);
      }
      .navbar-brand {
        font-weight: 700;
        color: #0d6efd !important;
      }
    </style>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg bg-white">
      <div class="container">
        <a class="navbar-brand" href="#">
          
          Zelo
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto"> <li class="nav-item">
              <a class="nav-link active" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="?page=listar">Meu perfil</a>
            </li>

          </ul>
        </div>
      </div>
    </nav>
    <div class="container">
        <div class= "row">
            <div class="col mt-5">
                <?php
                if (!isset($_SESSION['user_id']) && @$_REQUEST['page'] !== 'login') {
    header('Location: ../login/html/index.html');
    exit;
}
    include("conexao.php");
    switch(@$_REQUEST["page"]){
        case"novo":
            include("novo_user.php");
        break;
        case"listar":
            include("listar_user.php");
        break;
        case"salvar":
            include("salvar_user.php");
        break;
        case"editar":
            include("editar_user.php");
        break;
        case "addbanco":
            include("addbanco/addbanco.php");
        break;
        default:

          $user_id = (int) $_SESSION['user_id'];


          // ========================
          // 🔗 PEGAR DADOS DO USUARIO
          // ========================
          $stmt = $conn->prepare("SELECT item_id FROM cadastro WHERE id = ?");
          $stmt->bind_param("i", $user_id);
          $stmt->execute();
          $result_user = $stmt->get_result();
          $user = $result_user ? $result_user->fetch_assoc() : null;
          $stmt->close();

          $item_id = $user['item_id'] ?? null;

          // ========================
          // 💰 SALDO
          // ========================
          $saldo = "Não disponível";

          if ($user_id) {
            $python = trim((string) shell_exec("where python 2>NUL"));
            $pythonCmd = "python";

            if (empty($python)) {
              $pyLauncher = trim((string) shell_exec("where py 2>NUL"));
              if (!empty($pyLauncher)) {
                $pythonCmd = "py";
              }
            }

            $script = realpath(__DIR__ . '/../../saldo.py');
            if ($script) {
              $cmd = $pythonCmd . " " . escapeshellarg($script) . " " . escapeshellarg((string) $user_id) . " 2>&1";
              $saida = shell_exec($cmd);
              $saida = trim((string) $saida);
              $saldo = $saida !== '' ? $saida : "Não disponível";
            }
          }

          echo "<h2>Saldo: R$ $saldo</h2>";

          // ========================
          // 📄 EXTRATO
          // ========================
            $stmt = $conn->prepare("
    SELECT description, amount, categoria, date, user_id 
    FROM transactions 
    WHERE user_id = ? 
    ORDER BY date DESC
");

if (!$stmt) {
    die("Erro prepare: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("Erro execute: " . $stmt->error);
}

$result_extrato = $stmt->get_result();
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result_extrato = $stmt->get_result();

          echo "<h3 class='mt-4'>Extrato</h3>";

          while ($row = $result_extrato->fetch_assoc()) {

              $cor = $row['amount'] < 0 ? "red" : "green";

              echo "<div style='border-bottom:1px solid #ccc; padding:10px;'>";
              echo "<strong>{$row['description']}</strong><br>";
              echo "<span style='color:$cor;'>R$ {$row['amount']}</span><br>";
              echo "<small>{$row['categoria']} | {$row['date']}</small>";
              echo "</div>";
            }

              $stmt->close();

        break;           
    }
 ?>                
            </div>
        </div>  
 </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
