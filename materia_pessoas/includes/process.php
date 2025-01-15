<!--Será o responsável por processar o envio do formulário, validações, e interagir com o banco de dados  -->

<?php
// Incluir funções relacionadas ao banco de dados
include 'db_functions.php';
include 'materia_functions.php';

// Definir variáveis globais
$materia = [];
$mensagem_sucesso = "";

// Verificar se foi feita a solicitação para adicionar uma nova matéria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_materia') {
        try {
            // Cadastrar a matéria usando a função de cadastro
            $materia = cadastrarMateria($_POST, $pdo);
            $mensagem_sucesso = 'Matéria cadastrada com sucesso!';
        } catch (PDOException $e) {
            $mensagem_sucesso = ''; 
            echo '<div class="alert alert-danger">Erro ao cadastrar a Matéria: ' . htmlspecialchars($e->getMessage()) . '</div>';
        } catch (Exception $e) {
            $mensagem_sucesso = ''; 
            echo '<div class="alert alert-danger">Erro: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

// Buscar dados da matéria para edição
$mate_bole_cod = isset($_GET['mate_bole_cod']) ? (int)$_GET['mate_bole_cod'] : 0;
if ($mate_bole_cod > 0) {
    $materia = buscarMateria($mate_bole_cod, $pdo);
}

?>
