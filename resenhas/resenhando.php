<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: loginCadastro.php");
    exit();
}

// Conexão com o banco de dados
$host = 'localhost';
$db = 'resenhas';
$user = 'root';
$pass = '';

// Criar conexão
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Buscar as resenhas apenas do usuário logado
$usuario_id = $_SESSION['usuario_id'];

// Configurações de paginação
$resenhasPorPagina = 10; // Número de resenhas por página
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$paginaAtual = max($paginaAtual, 1); // Garantir que a página atual seja pelo menos 1

// Calcular o offset para a consulta SQL
$offset = ($paginaAtual - 1) * $resenhasPorPagina;

// Inicializa a variável $query como uma string vazia
$query = '';

// Verifica se a query foi passada pela URL
if (isset($_GET['query'])) {
    $query = $_GET['query'];
}

// Prepara a consulta SQL
if ($query) {
    $sqlCount = "SELECT COUNT(*) AS total FROM resenhas WHERE usuario_id = '$usuario_id' AND (titulo LIKE '%$query%' OR autor LIKE '%$query%')";
    $sql = "SELECT * FROM resenhas WHERE usuario_id = '$usuario_id' AND (titulo LIKE '%$query%' OR autor LIKE '%$query%') LIMIT $resenhasPorPagina OFFSET $offset";
} else {
    $sqlCount = "SELECT COUNT(*) AS total FROM resenhas WHERE usuario_id = '$usuario_id'";
    $sql = "SELECT * FROM resenhas WHERE usuario_id = '$usuario_id' LIMIT $resenhasPorPagina OFFSET $offset";
}

$resultCount = $conn->query($sqlCount);
$totalResenhas = $resultCount->fetch_assoc()['total'];
$totalPaginas = ceil($totalResenhas / $resenhasPorPagina);

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resenhalizando</title>
    <link rel="stylesheet" href="resenhando.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="iconeS.png" type="image/x-icon">
</head>

<script>
    function showModal(modalId) {
        document.getElementById(modalId).classList.add("show-modal");
    }

    function hideModal(modalId) {
        document.getElementById(modalId).classList.remove("show-modal");
    }

    //BARRA DE PESQUISA
    document.getElementById("searchButton").addEventListener("click", function () {
        const searchBar = document.getElementById("search-input");
        const searchText = searchBar.value.trim(); // Remove espaços extras

        if (searchText) {
            window.location.href = `resenhando.php?query=${encodeURIComponent(searchText)}`;
        } else {
            alert("Digite um termo para buscar.");
        }
    });
</script>

<body>
    <div class="cabecalho">
        <a href="resenhando.php"><h1>Resenhalizando</h1></a>
        <div class="bar-pesq">
            <form action="resenhando.php" method="GET">
                <input type="text" name="query" id="search-input" class="search-input" placeholder="Pesquisar...">
                <button type="submit" id="searchButton" class="btn-pesq">Pesquisar</button>

            </form>
        </div>
        <button onclick="location.href='logout.php'" class="btn-logout">Logout</button>
    </div>

    <div class="conteudo">
        <div class="botao">
            <a href="postando.php" class="ir-postar"><i class="bi bi-plus"></i></a>
        </div>

        <div class="container">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php $modalId = 'modal_' . $row['id']; // ID único para cada modal ?>
                    <div class="card" onclick="showModal('<?= $modalId ?>')">
                        <img src="<?= $row['capa'] ?>" alt="Capa do Livro" class="capa-livro">
                        <div class="tilAut">
                            <h2><?= $row['titulo'] ?></h2>
                            <p>Autor: <?= $row['autor'] ?></p>
                        </div>
                        <p class="stars"><?= str_repeat('★', $row['estrelas']) ?></p>
                        <a href="editar_pagina.php?id=<?= $row['id'] ?>" class="btn-editar"><i class="bi bi-pencil-fill"></i></a>
                    </div>

                    <!-- Modal específico para cada card -->
                    <div class="modal" id="<?= $modalId ?>">
                        <div class="modal-content">
                            <span onclick="hideModal('<?= $modalId ?>')">&times;</span>
                            <h2><?= $row['titulo'] ?></h2>
                            <img src="<?= $row['capa'] ?>" alt="Capa do Livro" class="capa-livroPop">
                            <p>Autor: <?= $row['autor'] ?></p>
                            <p class="starsPop"><?= str_repeat('★', $row['estrelas']) ?></p>
                            <p>Resenha: <?= $row['sinopse'] ?></p>
                            <p class="data">Data de Término: <?= $row['data_termino'] ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="erro-resenha">Nenhuma resenha ainda.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="resenhando.php?pagina=<?= $i ?>&query=<?= urlencode($query) ?>" class="page <?= $i === $paginaAtual ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>


    </div>

</body>
<footer style="background-color: #701cab; text-align: center; padding: 20px; position: relative; color:white;">
    <div>
        <p>&copy; 2024 Resenhalizando. Todos os direitos reservados.</p>
        <p>
        <a href="mailto:mithology@gmail.com" style="text-decoration: none; color: #e170f9;">
                Envie-nos um e-mail com ideias e duvidas
            </a>
        </p>
        <p>
        <a href="mailto:mithology@gmail.com" style="text-decoration: none; color: #e170f9;">
                mithology@gmail.com
            </a>
        </p>
    </div>
</footer>

</html>

<?php
$conn->close();
?>
