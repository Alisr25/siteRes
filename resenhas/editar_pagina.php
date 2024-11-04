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
$titulo = $autor = $estrelas = $sinopse = $data_termino = "";
$capa_atual = "";

// Verifica se o ID da resenha foi passado
if (isset($_GET['id'])) {
    $id_resenha = (int)$_GET['id'];
    
    // Carregar dados da resenha
    $sql = "SELECT * FROM resenhas WHERE id = $id_resenha";
    $resultado = $conn->query($sql);
    
    if ($resultado->num_rows > 0) {
        $resenha = $resultado->fetch_assoc();
        $titulo = $resenha['titulo'];
        $autor = $resenha['autor'];
        $estrelas = $resenha['estrelas'];
        $sinopse = $resenha['sinopse'];
        $data_termino = $resenha['data_termino'];
        $capa_atual = $resenha['capa'];
    } else {
        $mensagem = "Resenha não encontrada.";
    }
}

// Processar o formulário ao ser enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['titulo']) && !empty($_POST['autor']) && !empty($_POST['estrelas']) && !empty($_POST['sinopse']) && !empty($_POST['data_termino'])) {
        $titulo = $conn->real_escape_string($_POST['titulo']);
        $autor = $conn->real_escape_string($_POST['autor']);
        $estrelas = (int)$_POST['estrelas'];
        $sinopse = $conn->real_escape_string($_POST['sinopse']);
        $data_termino = $_POST['data_termino'];

        // Processar o upload da imagem se um novo arquivo foi enviado
        $capa = $_FILES['capa'];
        if ($capa['error'] === UPLOAD_ERR_OK) {
            $capa_nome = time() . '_' . basename($capa['name']);
            $capa_destino = 'uploads/' . $capa_nome;

            // Move a imagem para a pasta de uploads
            if (move_uploaded_file($capa['tmp_name'], $capa_destino)) {
                $capa_atual = $capa_destino; // Atualiza para o novo caminho
            } else {
                $mensagem = "Erro ao mover a imagem para a pasta de uploads.";
            }
        } else {
            // Se não há novo arquivo, mantém a capa atual
            $capa_atual = $capa_atual; 
        }

        // Atualiza a resenha no banco de dados
        $sql = "UPDATE resenhas SET 
                titulo = '$titulo', 
                autor = '$autor', 
                estrelas = '$estrelas', 
                sinopse = '$sinopse', 
                data_termino = '$data_termino', 
                capa = '$capa_atual' 
                WHERE id = $id_resenha";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['mensagem'] = "Resenha editada com sucesso!";
            header("Location: resenhando.php"); // Redireciona após a edição
            exit();
        } else {
            $mensagem = "Erro ao editar: " . $conn->error;
        }
    } else {
        $mensagem = "Preencha todos os campos do formulário.";
    }
}

$conn->close();

if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']); 
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="iconeS.png" type="image/x-icon">

    <title>Editar Resenha</title>
    <link rel="stylesheet" href="postando.css">
</head>
<style>
    .centro {
        text-align: center;
    }
</style>
<body>

    <a href="resenhando.php" class="btn-voltar"><i class="bi bi-arrow-left-short"></i></a>
    
    <?php if ($mensagem): ?>
        <div class="mensagem"><?php echo $mensagem; ?></div>
    <?php endif; ?>
    
    <div class="container">
        <form method="POST" enctype="multipart/form-data">
        <?php if ($capa_atual): ?>
            <div style="text-align: center;">
                <img src="<?php echo $capa_atual; ?>" alt="Capa Atual" width="100"><br>
            </div>
        <?php endif; ?>
            <input type="file" name="capa" accept="image/*">
            <input type="text" name="titulo" placeholder="Título do Livro" value="<?php echo htmlspecialchars($titulo); ?>" required>
            <input type="text" name="autor" placeholder="Autor do Livro" value="<?php echo htmlspecialchars($autor); ?>" required>
            <input type="number" name="estrelas" placeholder="Estrelas (1 a 5)" min="1" max="5" value="<?php echo htmlspecialchars($estrelas); ?>" required>
            <textarea name="sinopse" placeholder="Resenha" required><?php echo htmlspecialchars($sinopse); ?></textarea>
            <input type="date" name="data_termino" value="<?php echo htmlspecialchars($data_termino); ?>" required>
            <button type="submit">Atualizar Resenha</button>
        </form>
    </div>
    
</body>
</html>
