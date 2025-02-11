<?php
session_start();

// Inclua seu arquivo de conexão com o banco (ajuste o caminho conforme necessário)
include $_SERVER['DOCUMENT_ROOT'] . '/boletim/db.php';
// Conexão com o banco de dados



try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 1. Captura as variáveis enviadas via POST
        //    Se algum campo não for enviado, usa valor padrão
        $fk_mate_bole_cod       = isset($_POST['mate_bole_cod'])      ? (int) $_POST['mate_bole_cod']        : 0;
        $fk_poli_mili_matricula = isset($_POST['matriculaPM'])        ? (int) $_POST['matriculaPM']          : 0;
        $fk_poli_lota_cod       = isset($_POST['unidade'])            ? (int) $_POST['unidade']              : null;
        // O campo de posto/graduação deverá vir com o ID numérico
        $fk_index_post_grad_cod = isset($_POST['postoGraduacao'])     ? (int) $_POST['postoGraduacao']       : null;
        // Se data não for enviada, usaremos a data padrão '2000-01-01'
        $pess_mate_data_inicio  = !empty($_POST['dataInicio'])        ? $_POST['dataInicio']               : '2000-01-01';
        $pess_mate_data_fim     = !empty($_POST['dataFim'])           ? $_POST['dataFim']                  : '2000-01-01';
        $pess_mate_anobase      = isset($_POST['anoBase'])            ? (int) $_POST['anoBase']            : 0;

        // Se "fk_tiat_cod" for sempre 1, fixamos esse valor
        $fk_tiat_cod = 1;

        // 2. Monta a instrução SQL de INSERT de acordo com a estrutura da tabela
        $sql = "INSERT INTO bg.pessoa_materia (
                    fk_poli_lota_cod,
                    fk_mate_bole_cod,
                    pess_mate_data_inicio,
                    pess_mate_data_fim,
                    fk_index_post_grad_cod,
                    pess_mate_anobase,
                    fk_poli_mili_matricula,
                    fk_tiat_cod
                ) VALUES (
                    :fk_poli_lota_cod,
                    :fk_mate_bole_cod,
                    :pess_mate_data_inicio,
                    :pess_mate_data_fim,
                    :fk_index_post_grad_cod,
                    :pess_mate_anobase,
                    :fk_poli_mili_matricula,
                    :fk_tiat_cod
                )";

        // 3. Prepara a query e vincula os parâmetros
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':fk_poli_lota_cod',       $fk_poli_lota_cod,       PDO::PARAM_INT);
        $stmt->bindParam(':fk_mate_bole_cod',       $fk_mate_bole_cod,       PDO::PARAM_INT);
        $stmt->bindParam(':pess_mate_data_inicio',  $pess_mate_data_inicio);
        $stmt->bindParam(':pess_mate_data_fim',     $pess_mate_data_fim);
        $stmt->bindParam(':fk_index_post_grad_cod', $fk_index_post_grad_cod, PDO::PARAM_INT);
        $stmt->bindParam(':pess_mate_anobase',      $pess_mate_anobase,      PDO::PARAM_INT);
        $stmt->bindParam(':fk_poli_mili_matricula', $fk_poli_mili_matricula, PDO::PARAM_INT);
        $stmt->bindParam(':fk_tiat_cod',            $fk_tiat_cod,            PDO::PARAM_INT);

        // 4. Executa a query e retorna o resultado em JSON
        if ($stmt->execute()) {
            $novoId = $pdo->lastInsertId();
            echo json_encode([
                'success'  => true,
                'mensagem' => 'Registro inserido com sucesso!',
                'novoId'   => $novoId
            ]);
        } else {
            echo json_encode([
                'success'  => false,
                'mensagem' => 'Falha ao inserir registro.'
            ]);
        }
        exit;
    }

    echo json_encode([
        'success'  => false,
        'mensagem' => 'Método inválido.'
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Exceção: ' . $e->getMessage()
    ]);
    exit;
}
?>
