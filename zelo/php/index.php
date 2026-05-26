<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zelo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style1.css">
  </head>
  <body>

    <nav class="navbar navbar-expand-lg">
      <div class="container">
        
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="?page=listar">Meu perfil</a>
            </li>

          </ul>
        </div>
      </div>
    </nav>
    <nav class="sidebar">
      <a href="index.php" class="logo-link">
        <img class="logo" src="../assets/logo2.png" alt="Logo Zelo">
      </a>
      <ul class="side-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="?page=suporte">Ajuda</a></li>
      </ul>
    </nav>
    <div class="main-area">
      <div class="main-content">
              <?php
                
                if (!isset($_SESSION['user_id']) && @$_REQUEST['page'] !== 'login') {
                  header('Location: ../login/html/login.html');
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
                    include("editar_user.html");
                    break;
                  case "addbanco":
                    include("addbanco/addbanco.php");
                    break;
                  case "suporte":
                    include("suporte.php");
                    break;
                  case "solicitar_suporte":
                    include("solicitar_suporte.php");
                    break;
                  case "salvar_suporte":
                    include("salvar_suporte.php");
                    break;
                    case "admin_suporte":
                    include("admin_suporte.php");
                    break;
                     case "responder_suporte":
                    include("responder_suporte.php");
                    break;
                     case "salvar_resposta_suporte":
                    include("salvar_resposta_suporte.php");
                    break;
                  case "abrir_ticket":
                    include("abrir_ticket.php");
                    break;
                  case "salvar_ticket":
                    include("salvar_ticket.php");
                    break;
                  case "chat_usuario":
                    include("chat_usuario.php");
                    break;
                  case "api_chat":
                    include("api_chat.php");
                    break;
                  case "fechar_ticket":
                    include("fechar_ticket.php");
                    break;
                  default:
                    $user_id = (int) $_SESSION['user_id']; 


                    
                    $stmt = $conn->prepare("SELECT nome, item_id, saldo FROM cadastro WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result_user = $stmt->get_result();
                    $user = $result_user ? $result_user->fetch_assoc() : null;
                    $stmt->close();
                    
                    $nomeUsuario = $user['nome'] ?? 'Usuário'; 
                    $item_id = $user['item_id'] ?? null;
                    $saldoBanco = $user['saldo'] ?? null; 

                   

                    if ($saldoBanco !== null && $saldoBanco !== '') {
                      $saldo = number_format((float) $saldoBanco, 2, ',', '.');
                      echo "
                      <h3 class='saudacao'>Bem-vindo, $nomeUsuario</h3>
                      <div class='container-saldo'>
                        <div class='saldo-wrapper'>
                          <h1 class='saldo blur-saldo' id='saldoValue'>R$$saldo</h1>
                          <button class='btn-toggle-saldo' id='toggleSaldoBtn' onclick='toggleSaldoVisibility()' title='Mostrar saldo'>
                            <svg width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
                              <path d='M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24'></path>
                              <line x1='1' y1='1' x2='23' y2='23'></line>
                            </svg>
                          </button>
                        </div>
                      </div>";
                    }
                    else {
                      echo "
                        <div class='page-container'>
                          <h1 class='titulo-pagina'>Conecte seu banco</h1>
                          <p>Tenha acesso completo aos recursos da Zelo</p>
                          <a href='?page=addbanco' class='connect-bank-link'>+ Adicionar banco</a>
                        </div>
                        ";
                    }

                    
                    $stmt = $conn->prepare("
                      SELECT description, amount, categoria, date, user_id, categoria_editada 
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
                    echo "<div class='page-container'>";
                    if ($saldoBanco !== null && $saldoBanco !== '') {
                      
                        
                        echo "<h3 class='mt-4'>Extrato</h3>";
                         
                        while ($row = $result_extrato->fetch_assoc()) {
                          $cor = $row['amount'] < 0 ? "red" : "green";
                          echo "<div style='border-bottom:1px solid #ccc; padding:10px;'>";
                          echo "<strong>{$row['description']}</strong><br>";
                          echo "<span style='color:$cor;'>R$ {$row['amount']}</span><br>";
                          echo "<small>{$row['categoria_editada']} | {$row['date']}</small>";
                          echo "</div>";
                      
                      }
                    }
                    
                    $stmt->close();
                    break; 
                    echo "</div>";          
                }
              ?>                
        </div>  
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSaldoVisibility() {
            const saldoElement = document.getElementById('saldoValue');
            const toggleBtn = document.getElementById('toggleSaldoBtn');
            
            if (saldoElement) {
                saldoElement.classList.toggle('blur-saldo');
                
                
                const isBlurred = saldoElement.classList.contains('blur-saldo');
                if (isBlurred) {
                    toggleBtn.title = 'Ocultar saldo';
                    toggleBtn.innerHTML = `
                        <svg width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
                            <path d='M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24'></path>
                            <line x1='1' y1='1' x2='23' y2='23'></line>
                        </svg>
                    `;
                } else {
                    toggleBtn.title = 'Mostrar saldo';
                    toggleBtn.innerHTML = `
                        <svg width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'>
                            <path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path>
                            <circle cx='12' cy='12' r='3'></circle>
                        </svg>
                    `;
                }
            }
        }
    </script>
  </body>
</html>
