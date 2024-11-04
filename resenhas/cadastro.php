<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
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
        $user = 'root';
        $pass = '';

        $conn = new mysqli($host, $user, $pass, $db);

        if ($conn->connect_error) {
            die("<div class='mensagem-erro'>Conexão falhou: " .         $conn->connect_error . "</div>");
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $conn->real_escape_string($_POST['email']);
            $senha = $_POST['senha'];
            $confirmar_senha = $_POST['confirmar_senha'];

           if ($senha !== $confirmar_senha) {
                echo "<div class='mensagem-erro'>As senhas não coincidem.</div>";
                exit();
           }

           $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

            $sql_verificar_email = "SELECT * FROM usuarios WHERE email='$email'";
            $resultado_email = $conn->query($sql_verificar_email);

           if ($resultado_email->num_rows > 0) {
                echo "<div class='mensagem-erro'>Este email já está registrado.         Por favor, escolha outro.</div>";
           } else {
               $sql_inserir = "INSERT INTO usuarios (email, senha) VALUES   ('$email', '$senha_hash')";

                if ($conn->query($sql_inserir) === TRUE) {
                   echo "<div class='mensagem-sucesso'>Cadastro realizado com   sucesso!</div>";
                } else {
                    echo "<div class='mensagem-erro'>Erro: " . $sql_inserir .       "<br>" . $conn->error . "</div>";
                }
            }
        }

        $conn->close();
        ?>
    </div>



</body>
</html>
