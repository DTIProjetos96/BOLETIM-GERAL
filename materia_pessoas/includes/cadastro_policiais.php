<?php
include_once 'includes/db.php';
include_once 'includes/user_functions.php';

// Verifica se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'matricula' => $_POST['matricula'] ?? null,
        'posto_graduacao' => $_POST['postoGraduacao'] ?? null,
        'unidade' => $_POST['unidade'] ?? null,
        'data_inicio' => $_POST['dataInicial'] ?? '2000-01-01',
        'data_fim' => $_POST['dataFinal'] ?? '2000-01-01',
        'ano_base' => $_POST['anoBase'] ?? 0,
        'mate_bole_cod' => $_POST['mate_bole_cod'] ?? null,
        'posto_grad_atual' => $_POST['postoGraduacaoAtual'] ?? null
    ];

    // Verifica se os campos obrigatórios estão preenchidos
    if (!$dados['matricula'] || !$dados['mate_bole_cod']) {
        echo json_encode(['success' => false, 'error' => 'Campos obrigatórios ausentes!']);
        exit;
    }

    // Chama a função para adicionar ao banco
    $resultado = adicionarPolicialMateria($pdo, $dados);

    // Retorna a resposta em JSON
    echo json_encode($resultado);
    exit;
}
?>
