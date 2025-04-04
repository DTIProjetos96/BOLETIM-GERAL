<?php
// No arquivo user_functions.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Inclua o arquivo de conexão
$root = $_SERVER['DOCUMENT_ROOT'] . '/Boletim/db.php';
if (file_exists($root)) {
    include_once $root;
} else {
    die(json_encode(['error' => 'Erro ao incluir o arquivo de conexão.']));
}

// Ative os erros para depuração (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifique se a conexão foi estabelecida
if (!isset($pdo)) {
    die(json_encode(['error' => 'Falha na conexão com o banco de dados.']));
}


function getSubunidadesUsuario($pdo, $login) {
    // Buscar subunidades para as quais o usuário tem permissão, etc.
    $stmt = $pdo->prepare("
        SELECT subu_cod, concat(subu_descricao, ' - ', unid_descricao, ' - ', coma_descricao) as descricao
        FROM public.vw_comando_unidade_subunidade
        WHERE subu_cod IN (
            SELECT fk_subunidade
            FROM bg.vw_permissao
            WHERE fk_login = :login AND perm_ativo = 1
        )
        ORDER BY subu_descricao, unid_descricao, coma_descricao
    ");
    $stmt->execute(['login' => $login]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




// Função para buscar os dados do policial com base na matrícula
function buscarDadosPolicial($pdo, $matricula) {
    if ($matricula) {
        try {
            // Consulta para buscar os dados do policial, incluindo a unidade
            $stmt = $pdo->prepare("
                SELECT matricula, nome, pg_descricao, unidade
                FROM vw_policiais_militares
                WHERE matricula = :matricula
                LIMIT 1
            ");
            $stmt->execute(['matricula' => $matricula]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados) {
                return ['success' => true, 'dados' => $dados];
            } else {
                return ['success' => false, 'message' => 'Policial não encontrado.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    } else {
        return ['success' => false, 'message' => 'Matrícula não informada.'];
    }
}

// Verifica se a ação passada é válida
if ($_POST['action'] === 'buscar_policial_militar') {
    $term = isset($_POST['term']) ? $_POST['term'] : '';

    try {
        $query = "SELECT matricula AS value, nome AS label, id_pg AS postoGradCod, pg_descricao, unidade
                  FROM vw_policiais_militares
                  WHERE nome ILIKE :term OR CAST(matricula AS TEXT) LIKE :term
                  LIMIT 10";

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Adiciona os campos necessários ao autocomplete
        foreach ($results as &$result) {
            $result['postoGradCod'] = isset($result['postoGradCod']) ? (int)$result['postoGradCod'] : 0;
            $result['pg_descricao'] = $result['pg_descricao'] ?? 'Não especificado';
            $result['unidade'] = $result['unidade'] ?? 'Não especificado';
        }

        echo json_encode($results);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}


// // Recupera as opções para o campo Posto/Graduação com ID e Descrição
// function buscarPostosGraduacoes($pdo) {
//     try {
//         $stmt_pg = $pdo->query("SELECT DISTINCT id_pg, pg_descricao FROM bg.vw_policiais_militares ORDER BY pg_descricao");
//         return $stmt_pg->fetchAll(PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         error_log("Erro ao buscar postos/graduações: " . $e->getMessage());
//         return [];
//     }
// }

// // Busca as unidades com código e unidade
// function buscarUnidades($pdo) {
//     try {
//         $stmt_unidades = $pdo->query("SELECT DISTINCT cod_unidade, unidade FROM bg.vw_policiais_militares ORDER BY unidade");
//         return $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
//     } catch (PDOException $e) {
//         error_log("Erro ao buscar unidades: " . $e->getMessage());
//         return [];
//     }
// }

?> 