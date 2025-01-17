<?php
session_start(); // Inicia a sessão

include '../db.php'; // Conexão com o banco de dados
include 'includes/utils.php'; // Funções utilitárias
include 'includes/materia_functions.php'; // Funções relacionadas a matéria

$mate_bole_cod = isset($_GET['mate_bole_cod']) ? (int)$_GET['mate_bole_cod'] : 0;
$materia = [];
$show_iframe = true; // Flag do iframe sempre visível

// Defina o login do usuário manualmente para fins de teste
$user_login = '452912';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mate_bole_cod > 0 ? 'Editar Matéria' : 'Cadastrar Matéria'; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    
    
</head>

<body>
    <div class="container">
        <!-- Mensagem de Sucesso -->
        <?php if (!empty($mensagem_sucesso)): ?>
            <div class="alert alert-success">
                <?php echo $mensagem_sucesso; ?>
            </div>
        <?php endif; ?>

        <h2><?php echo $mate_bole_cod > 0 ? 'Editar Matéria' : 'Cadastrar Matéria'; ?></h2>

        <!-- Incluir o formulário -->
        <?php include 'formulario/form.php'; ?>

</body>

</html>