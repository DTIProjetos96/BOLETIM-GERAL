<?php
// Função para verificar se a requisição é do tipo Ajax
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

if (isAjaxRequest() && isset($_GET['action']) && $_GET['action'] === 'fetch_assunto_texto') {
    $assu_espe_cod = isset($_GET['assu_espe_cod']) ? (int)$_GET['assu_espe_cod'] : 0;

    try {
        // Prepare a consulta SQL para buscar o Assunto Geral e o Texto do Assunto Específico
        $stmt = $pdo->prepare("
            SELECT assu_espe_texto, assu_gera_cod, assu_gera_descricao
            FROM bg.vw_assunto_concatenado
            WHERE assu_espe_cod = :assu_espe_cod
            LIMIT 1
        ");
        $stmt->execute(['assu_espe_cod' => $assu_espe_cod]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retorne a resposta como JSON
        echo json_encode([
            'success' => true,
            'assu_espe_texto' => $result['assu_espe_texto'] ?? '',
            'assu_gera_cod' => $result['assu_gera_cod'] ?? '',
            'assu_gera_descricao' => $result['assu_gera_descricao'] ?? '',
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
