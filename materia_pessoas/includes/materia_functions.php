<?php

// materia_functions.php

//Recupera matéria do BD
function buscarMateria($pdo, $mate_bole_cod) {
    try {
        // Recuperar os dados da matéria
        $sql = "SELECT * FROM bg.materia_boletim WHERE mate_bole_cod = :mate_bole_cod";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':mate_bole_cod', $mate_bole_cod, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $materia = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Recuperar Assunto Geral com base no Assunto Específico
            if (!empty($materia['fk_assu_espe_cod'])) {
                $stmt_geral = $pdo->prepare("
                    SELECT assu_gera_cod, assu_gera_descricao 
                    FROM bg.vw_assunto_concatenado 
                    WHERE assu_espe_cod = :assu_espe_cod
                    LIMIT 1
                ");
                $stmt_geral->execute(['assu_espe_cod' => $materia['fk_assu_espe_cod']]);
                $assunto_geral = $stmt_geral->fetch(PDO::FETCH_ASSOC);
                $materia['fk_assu_gera_cod'] = $assunto_geral['assu_gera_cod'] ?? '';
                $materia['fk_assu_gera_descricao'] = $assunto_geral['assu_gera_descricao'] ?? '';
            }
            return $materia;
        } else {
            return null;
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar matéria: " . $e->getMessage());
        return null;
    }
}
// Busca do texto de um assunto específico e manipulação de uma matéria
if (isAjaxRequest() && isset($_GET['action']) && $_GET['action'] === 'fetch_assunto_texto') {
    $assu_espe_cod = isset($_GET['assu_espe_cod']) ? (int)$_GET['assu_espe_cod'] : 0;

    try {
        $stmt = $pdo->prepare("
            SELECT assu_espe_texto, assu_gera_cod, assu_gera_descricao
            FROM bg.vw_assunto_concatenado
            WHERE assu_espe_cod = :assu_espe_cod
            LIMIT 1
        ");
        $stmt->execute(['assu_espe_cod' => $assu_espe_cod]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

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

function adicionarMateria($pdo, $data) {
    try {
        $stmt = $pdo->prepare('
            INSERT INTO bg.materia_boletim (
                mate_bole_texto, 
                mate_bole_data, 
                fk_tipo_docu_cod, 
                fk_assu_espe_cod, 
                fk_assu_gera_cod,  
                mate_bole_nr_doc, 
                mate_bole_data_doc, 
                fk_subu_cod
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING mate_bole_cod
        ');

        $stmt->execute([
            $data['mate_bole_texto'], 
            $data['mate_bole_data'], 
            $data['fk_tipo_docu_cod'],
            $data['fk_assu_espe_cod'], 
            $data['fk_assu_gera_cod'], 
            $data['mate_bole_nr_doc'], 
            $data['mate_bole_data_doc'], 
            $data['fk_subu_cod']
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['mate_bole_cod'] ?? null;

    } catch (PDOException $e) {
        error_log("Erro ao adicionar matéria: " . $e->getMessage());
        return null;
    }
}


// Função para salvar uma nova matéria
// Verifique se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_materia') {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO bg.materia_boletim (
                    mate_bole_texto, 
                    mate_bole_data, 
                    fk_tipo_docu_cod, 
                    fk_assu_espe_cod, 
                    fk_assu_gera_cod,  
                    mate_bole_nr_doc, 
                    mate_bole_data_doc, 
                    fk_subu_cod
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING mate_bole_cod
            ');
            $stmt->execute([
                $_POST['mate_bole_texto'], 
                $_POST['mate_bole_data'], 
                $_POST['fk_tipo_docu_cod'],
                $_POST['fk_assu_espe_cod'], 
                $_POST['fk_assu_gera_cod'], // ADICIONADO AQUI! 
                $_POST['mate_bole_nr_doc'], 
                $_POST['mate_bole_data_doc'], 
                $_POST['fk_subu_cod']
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $mate_bole_cod = $result['mate_bole_cod'];

            if (!$mate_bole_cod) {
                throw new Exception("Falha ao recuperar o código da Matéria.");
            }

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


// Função para editar uma matéria
function editarMateria($pdo, $mate_bole_cod, $data) {
    try {
        $stmt = $pdo->prepare('
            UPDATE bg.materia_boletim SET
                mate_bole_texto = ?, 
                mate_bole_data = ?, 
                fk_tipo_docu_cod = ?, 
                fk_assu_espe_cod = ?, 
                mate_bole_nr_doc = ?, 
                mate_bole_data_doc = ?, 
                fk_subu_cod = ?
            WHERE mate_bole_cod = ?
        ');
        $stmt->execute([
            $data['mate_bole_texto'], 
            $data['mate_bole_data'], 
            $data['fk_tipo_docu_cod'],
            $data['fk_assu_espe_cod'], 
            $data['mate_bole_nr_doc'], 
            $data['mate_bole_data_doc'], 
            $data['fk_subu_cod'], 
            $mate_bole_cod
        ]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Função para buscar os dados de uma matéria para edição
function buscarMateriaEdicao($pdo, $mate_bole_cod) {
    $sql = "
        SELECT 
            mb.*, 
            ag.assu_gera_descricao AS assunto_geral_descricao, 
            ag.assu_gera_cod AS assunto_geral_cod, 
            CONCAT(su.subu_descricao, ' - ', su.unid_descricao, ' - ', su.coma_descricao) AS unidade_descricao
        FROM bg.materia_boletim mb
        LEFT JOIN bg.assunto_geral ag ON mb.fk_assu_gera_cod = ag.assu_gera_cod
        LEFT JOIN public.vw_comando_unidade_subunidade su ON mb.fk_subu_cod = su.subu_cod
        WHERE mb.mate_bole_cod = :mate_bole_cod
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':mate_bole_cod', $mate_bole_cod, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $materia = $stmt->fetch(PDO::FETCH_ASSOC);

        // **Caso não tenha Assunto Geral, busque pelo Assunto Específico**
        if (empty($materia['assunto_geral_cod']) && !empty($materia['fk_assu_espe_cod'])) {
            $stmt_geral = $pdo->prepare("
                SELECT assu_gera_cod, assu_gera_descricao 
                FROM bg.vw_assunto_concatenado 
                WHERE assu_espe_cod = :assu_espe_cod
                LIMIT 1
            ");
            $stmt_geral->execute(['assu_espe_cod' => $materia['fk_assu_espe_cod']]);
            $assunto_geral = $stmt_geral->fetch(PDO::FETCH_ASSOC);
            $materia['fk_assu_gera_cod'] = $assunto_geral['assu_gera_cod'] ?? '';
            $materia['assu_gera_descricao'] = $assunto_geral['assu_gera_descricao'] ?? '';
        }

        return $materia;
    } else {
        return null;
    }
}





// Recupera as opções para o campo Tipo de Documento da matéria
function getTiposDocumento($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT tipo_docu_cod, tipo_docu_descricao FROM bg.tipo_documento ORDER BY tipo_docu_descricao");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Tratar erro, por exemplo, logando a mensagem
        error_log("Erro ao buscar tipos de documentos: " . $e->getMessage());
        return []; // Retorna um array vazio em caso de erro
    }
}

// Ação para salvar a edição no banco de dados
if (isset($_POST['action']) && $_POST['action'] === 'salvar_edicao') {
    $matricula = $_POST['matricula'];
    $novaUnidade = $_POST['unidade'];
    $novoIdPg = $_POST['id_pg']; // Recebemos o ID do posto/graduação, não o texto

    // Atualizar a unidade e o posto/graduação na tabela pessoa_materia
    $stmt = $pdo->prepare("UPDATE bg.pessoa_materia SET fk_poli_lota_cod = :unidade, fk_index_post_grad_cod = :id_pg WHERE fk_poli_mili_matricula = :matricula");
    $stmt->execute([
        'unidade' => $novaUnidade,
        'id_pg' => $novoIdPg,
        'matricula' => $matricula
    ]);

    echo json_encode(['success' => true]);
    exit;
}

// Recupera os Assuntos Específicos com o campo assu_espe_texto da matéria
function buscarAssuntosEspecificos($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT assu_espe_cod, assu_espe_descricao, assu_gera_cod, assu_gera_descricao, assu_espe_texto 
            FROM bg.vw_assunto_concatenado 
            ORDER BY assu_espe_descricao
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar Assunto Específico: " . $e->getMessage());
        return [];
    }
}


// Função para buscar o Assunto Geral com base no Assunto Específico
function buscarAssuntoGeralPorEspecifico($pdo, $assu_espe_cod) {
    try {
        // Prepara a consulta SQL para buscar os dados do Assunto Geral
        $stmt = $pdo->prepare("
            SELECT assu_gera_cod, assu_gera_descricao, assu_espe_texto
            FROM bg.vw_assunto_concatenado
            WHERE assu_espe_cod = :assu_espe_cod
            LIMIT 1
        ");
        // Executa a consulta passando o código do Assunto Específico
        $stmt->execute(['assu_espe_cod' => $assu_espe_cod]);
        // Obtém o resultado da consulta
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica se um resultado foi encontrado
        if ($result) {
            return [
                'success' => true,
                'assu_gera_cod' => $result['assu_gera_cod'],
                'assu_gera_descricao' => $result['assu_gera_descricao'],
                'assu_espe_texto' => $result['assu_espe_texto']
            ];
        } else {
            return ['success' => false, 'message' => 'Assunto Geral não encontrado'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


?>

