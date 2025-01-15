
<?php
// No arquivo user_functions.php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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


// Verifique se a requisição é um POST
// Verifica se a ação passada é válida
if (isset($_POST['action']) && $_POST['action'] === 'buscar_policial_militar') {
    $term = $_POST['term'] ?? '';

    // Verifica se o termo não está vazio
    if (!empty($term)) {
        try {
            $query = "SELECT matricula, nome FROM vw_pessoa_materia WHERE nome ILIKE :term LIMIT 10";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
            $stmt->execute();

            $resultados = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $resultados[] = [
                    'label' => $row['nome'],  // Nome do policial
                    'value' => $row['matricula'],  // Matrícula do policial
                ];
            }

            // Define o cabeçalho e retorna os dados JSON
            header('Content-Type: application/json');
            echo json_encode($resultados);
        } catch (PDOException $e) {
            // Retorna erro em formato JSON
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Erro ao buscar policiais: ' . $e->getMessage()]);
        }
    } else {
        // Retorna erro se o termo estiver vazio
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Termo de busca vazio.']);
    }
    exit; // Encerra o script
}

// Recupera as opções para o campo Posto/Graduação
function buscarPostosGraduacoes($pdo) {
    try {
        $stmt_pg = $pdo->query("SELECT DISTINCT pg_descricao FROM bg.vw_policiais_militares ORDER BY pg_descricao");
        return $stmt_pg->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar postos/graduações: " . $e->getMessage());
        return [];
    }
}

// Busca as unidades para o datalist
function buscarUnidades($pdo) {
    try {
        $stmt_unidades = $pdo->query("SELECT DISTINCT unidade FROM bg.vw_policiais_militares ORDER BY unidade");
        return $stmt_unidades->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar unidades: " . $e->getMessage());
        return [];
    }
}

?>
