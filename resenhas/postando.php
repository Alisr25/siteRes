<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: loginCadastro.php");
    exit();
}

$host = 'localhost';
$db = 'resenhas';
$user = 'root'; // Alterar conforme necessário
$pass = ''; // Alterar conforme necessário

// Criar conexão
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$mensagem = "";

// Processar o formulário ao ser enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $estrelas = (int)$_POST['estrelas'];
    $sinopse = $conn->real_escape_string($_POST['sinopse']);
    $data_termino = $_POST['data_termino'];
    
    // Obter o ID do usuário logado
    $usuario_id = $_SESSION['usuario_id'];

    // Processar o upload da imagem
    $capa = $_FILES['capa'];
    if ($capa['error'] === UPLOAD_ERR_OK) {
        $capa_nome = time() . '_' . basename($capa['name']);
        $capa_destino = 'uploads/' . $capa_nome;

        // Move a imagem para a pasta de uploads
        if (move_uploaded_file($capa['tmp_name'], $capa_destino)) {
            // Insere no banco de dados
            $sql = "INSERT INTO resenhas (titulo, autor, estrelas, sinopse, data_termino, capa, usuario_id) 
                    VALUES ('$titulo', '$autor', '$estrelas', '$sinopse', '$data_termino', '$capa_destino', '$usuario_id')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['mensagem'] = "Resenha postada com sucesso!";
                header("Location: postando.php"); // Redireciona para a mesma página ou outra página
                exit();
            } else {
                $mensagem = "Erro: " . $conn->error;
            }
        } else {
            $mensagem = "Erro ao enviar a imagem.";
        }
    } else {
        $mensagem = "Erro no upload da imagem: " . $capa['error'];
    }
}


$conn->close();

if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']); // Limpa a mensagem após exibi-la
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postando Resenha</title>
    <link rel="stylesheet" href="postando.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="iconeS.png" type="image/x-icon">
</head>
<body>

    <a href="resenhando.php" class="btn-voltar"><i class="bi bi-arrow-left-short"></i></a>
    

    <?php if ($mensagem): ?>
        <div class="mensagem"><?php echo $mensagem; ?></div> <!-- Exibe a mensagem -->
    <?php endif; ?>
    

    <div class="container">

        

        <form method="POST" enctype="multipart/form-data">
            
            <input type="file" name="capa" accept="image/*" required>
            <input type="text" name="titulo" placeholder="Título do Livro" required>
            <input type="text" name="autor" placeholder="Autor do Livro" required>
            <input type="number" name="estrelas" placeholder="Estrelas (0 a 5)" min="0" max="5" required>
            <textarea name="sinopse" placeholder="Resenha" required></textarea>
            <input type="date" name="data_termino" required>
        <button type="submit">Postar Resenha</button>
        </form>
    </div>
    
</body>
</html>
