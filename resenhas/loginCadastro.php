<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resenhalizando | Cadastro</title>
    <link rel="stylesheet" href="estilosMnsgns.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="iconeS.png" type="image/x-icon">
</head>
<body>
    <div class="container">

        <a href="loginCadastro.html" class="btn-fecha"><i class="bi bi-x"></i></a>
        
        <?php
        session_start();
        $host = 'localhost';
        $db = 'resenhas';
        $user = 'root'; // Altere para o seu usuário do banco de dados
        $pass = ''; // Altere para a sua senha do banco de dados

        // Conectar ao banco de dados
        $conn = new mysqli($host, $user, $pass, $db);

        // Verificar a conexão
        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        // Processar o login
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Verifica se as chaves estão definidas
            if (isset($_POST['email']) && isset($_POST['senha'])) {
                $email = $_POST['email'];
                $senha = $_POST['senha'];

                // Escapar strings para evitar SQL Injection
                $email = $conn->real_escape_string($email);

                // Consultar o banco de dados para verificar o usuário
                $sql = "SELECT * FROM usuarios WHERE email = '$email'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                   $user = $result->fetch_assoc(); // Pega o usuário retornado do banco

                    // Verifica a senha usando password_verify
                    if (password_verify($senha, $user['senha'])) {
                        // Usuário encontrado e senha correta
                        $_SESSION['usuario_id'] = $user['id']; // Armazena o ID do usuário na sessão
                        header("Location: resenhando.php"); // Redireciona para a página inicial
                
                        exit();
                    } else {
                       echo "<div class='mensagem-erro'>Senha incorreta.</div>";
                    }
               } else {
                    echo "<div class='mensagem-erro'>Usuário não encontrado.</div>";
               }
            } else {
                echo "<div class='mensagem-erro'>Por favor, preencha todos os campos.</div>";
            }
        }

        // Fechar a conexão
        $conn->close();
        ?>
    </div>



</body>
</html>
