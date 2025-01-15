<?php
// Conexão ao banco de dados (assumindo que o arquivo '../db.php' já faz isso)
include '../db.php'; 

// Função para buscar descrições
function buscarDescricao($pdo, $campo_codigo, $valor_codigo, $campo_descricao, $tabela) {
    $stmt = $pdo->prepare("SELECT $campo_descricao FROM $tabela WHERE $campo_codigo = :valor LIMIT 1");
    $stmt->execute(['valor' => $valor_codigo]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado[$campo_descricao] : 'N/A';
}

?>
