<?php
// Inclua o arquivo que contém a função buscarAssuntosEspecificos
include_once 'includes/materia_functions.php';
include_once 'includes/user_functions.php';


//login do usuário manualmente para fins de teste
$user_login = '452912';

$matricula = isset($_GET['matricula']) ? $_GET['matricula'] : ''; // Exemplo de matrícula

// Chame a função para buscar os assuntos específicos
$assuntosEspecificos = buscarAssuntosEspecificos($pdo);

//Chama função para buscar os tipos de documentos
$tipos_documento = getTiposDocumento($pdo);

// Chama a função para buscar os dados do policial
$dadosPolicialResponse = buscarDadosPolicial($pdo, $matricula);

$subunidades = getSubunidadesUsuario($pdo, $_SESSION['matricula']);

// Se a sessão não estiver setada, redirecione ou trate de outra forma
if (!isset($_SESSION['matricula'])) {
    die('Você precisa estar logado para acessar este formulário.');
}

// Agora pega a matrícula do usuário logado
$user_login = $_SESSION['matricula'];


// Verifica se é edição
$mate_bole_cod = isset($_GET['mate_bole_cod']) ? (int)$_GET['mate_bole_cod'] : 0;

// Inicializa o array da matéria
$materia = [];

// Se for edição, carrega os dados da matéria
if ($mate_bole_cod > 0) {
    $materia = buscarMateriaEdicao($pdo, $mate_bole_cod);
    if (!$materia) {
        echo "<div class='alert alert-danger'>Matéria não encontrada!</div>";
        $mate_bole_cod = 0; // Impede edição de algo inexistente
    } else {
        // ✅ Busca o Assunto Geral relacionado ao Assunto Específico
        if (!empty($materia['fk_assu_espe_cod'])) {
            $assuntoGeral = buscarAssuntoGeralPorEspecifico($pdo, $materia['fk_assu_espe_cod']);
            if ($assuntoGeral['success']) {
                $materia['fk_assu_gera_cod'] = $assuntoGeral['assu_gera_cod'];
                $materia['assu_gera_descricao'] = $assuntoGeral['assu_gera_descricao'];
            }
        }
    }
}

// Se for edição, e $mate_bole_cod for maior que 0, busque as pessoas associadas:
$pessoasAssociadas = ($mate_bole_cod > 0) ? buscarPessoasAssociadas($pdo, $mate_bole_cod) : [];

// Aqui você insere a verificação para buscar as pessoas associadas:
if ($mate_bole_cod > 0) {
    $pessoasAssociadas = buscarPessoasAssociadas($pdo, $mate_bole_cod);
    error_log("Pessoas associadas: " . print_r($pessoasAssociadas, true));
} else {
    $pessoasAssociadas = [];
}



// Atualizar os dados da matéria com a resposta do servidor, se existir
if (isset($response['success']) && $response['success']) {
    $materia['fk_assu_gera_cod'] = $response['assu_gera_cod'];
    $materia['assu_gera_descricao'] = $response['assu_gera_descricao'];
    $materia['mate_bole_texto'] = $response['assu_espe_texto'];
}


// Formata a data do documento no formato yyyy-MM-dd
$date_doc = null;
if (!empty($materia['mate_bole_data_doc'])) {
    try {
        $date = new DateTime($materia['mate_bole_data_doc']);
        $date_doc = $date->format('Y-m-d'); // Formata para o formato aceito pelo input
    } catch (Exception $e) {
        $date_doc = ''; // Valor padrão se a data for inválida
    }
}

?>

<head>
    <script src="js/assunto_geral_especifico.js"></script>
    <!-- <script src="js/autocomplete.js"></script> -->
    <!-- <script src="js/associar_pessoa_materia.js"></script> -->
</head>

<form method="POST" action="cad.php" enctype="multipart/form-data">
    <?php if ($mate_bole_cod > 0): ?>
        <input type="hidden" id="mate_bole_cod" name="mate_bole_cod" value="<?php echo htmlspecialchars($mate_bole_cod); ?>">
    <?php endif; ?>


    <!-- ASSUNTO ESPECÍFICO -->
    <div class="row">
        <div class="col-md-6">
            <label for="fk_assu_espe_cod" class="form-label">Assunto Específico</label>
            <select class="form-select" id="fk_assu_espe_cod" name="fk_assu_espe_cod" required>
                <option value="">Selecione</option>
                <?php foreach ($assuntosEspecificos as $assunto): ?>
                    <option value="<?= htmlspecialchars($assunto['assu_espe_cod']) ?>"
                        <?= ($materia['fk_assu_espe_cod'] == $assunto['assu_espe_cod']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($assunto['assu_espe_descricao']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>


        <!-- ASSUNTO GERAL -->
        <div class="col-md-6">
            <label for="fk_assu_gera_cod" class="form-label">Assunto Geral</label>
            <select class="form-select" id="fk_assu_gera_cod" name="fk_assu_gera_cod">
                <option value="">Selecione o Assunto Geral</option>
                <?php if (!empty($materia['fk_assu_gera_cod']) && !empty($materia['assu_gera_descricao'])): ?>
                    <option value="<?= htmlspecialchars($materia['fk_assu_gera_cod']) ?>" selected>
                        <?= htmlspecialchars($materia['assu_gera_descricao']) ?>
                    </option>
                <?php endif; ?>
            </select>
        </div>


    </div>

    <!-- NOME DA UNIDADE -->
    <div class="row">
        <div class="col-md-6">
            <label for="fk_subu_cod" class="form-label">Nome da Unidade</label>
            <select class="form-select" id="fk_subu_cod" name="fk_subu_cod" required>
                <option value="">Selecione</option>
                <?php foreach ($subunidades as $subunidade): ?>
                    <option value="<?= htmlspecialchars($subunidade['subu_cod']) ?>"
                        <?= ($materia['fk_subu_cod'] == $subunidade['subu_cod']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($subunidade['descricao']) ?>
                    </option>
                <?php endforeach; ?>
            </select>



        </div>

        <!-- DATA DA MATÉRIA -->
        <div class="col-md-6">
            <label for="mate_bole_data" class="form-label">Data da Matéria</label>
            <input type="date" class="form-control" id="mate_bole_data" name="mate_bole_data"
                value="<?= htmlspecialchars($materia['mate_bole_data'] ?? '') ?>" required>

        </div>
    </div>

    <!-- TIPO DE DOCUMENTO -->
    <div class="row">
        <div class="col-md-6">
            <label for="fk_tipo_docu_cod" class="form-label">Tipo de Documento</label>
            <select class="form-select" id="fk_tipo_docu_cod" name="fk_tipo_docu_cod" required>
                <option value="">Selecione</option>
                <?php foreach ($tipos_documento as $tipo): ?>
                    <option value="<?= htmlspecialchars($tipo['tipo_docu_cod']) ?>"
                        <?= ($materia['fk_tipo_docu_cod'] == $tipo['tipo_docu_cod']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($tipo['tipo_docu_descricao']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

        </div>

        <!-- NÚMERO DO DOCUMENTO -->
        <div class="col-md-6">
            <label for="mate_bole_nr_doc" class="form-label">Número do Documento</label>
            <input type="text" class="form-control" id="mate_bole_nr_doc" name="mate_bole_nr_doc"
                value="<?= htmlspecialchars($materia['mate_bole_nr_doc'] ?? '') ?>">

        </div>
    </div>

    <!-- DATA DO DOCUMENTO -->
    <div class="row">
        <div class="col-md-6">
            <label for="mate_bole_data_doc" class="form-label">Data do Documento</label>
            <input type="date" class="form-control" id="mate_bole_data_doc" name="mate_bole_data_doc"
                value="<?= htmlspecialchars($materia['mate_bole_data_doc'] ?? '') ?>">




        </div>
    </div>

    <!-- TEXTO DA MATeria -->
    <div class="row">
        <div class="col-md-12">
            <label for="mate_bole_texto" class="form-label">Texto da Matéria</label>
            <textarea class="form-control" id="mate_bole_texto" name="mate_bole_texto" rows="5" required><?= htmlspecialchars($materia['mate_bole_texto'] ?? '') ?></textarea>

        </div>
    </div>

    <!-- Botões Salvar e Cancelar -->
    <div class="button-group mt-4 mb-4"> <!-- Adicionei a classe mb-4 para adicionar o espaçamento entre os botões e a próxima seção -->
        <?php if ($mate_bole_cod > 0): ?>

            <button type="submit" class="btn btn-primary" id="btnSalvar">Atualizar</button>
        <?php else: ?>
            <!-- Botão Salvar -->
            <div class="button-group mt-4">
                <button type="submit" name="action" value="add_materia" class="btn btn-primary">Salvar</button>
                <a href="consulta_materia1.php" class="btn btn-secondary">Cancelar</a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Seção para Associar Pessoas à Matéria -->
    <div class="associar-pessoa-container"
        style="<?= $mate_bole_cod == 0 ? 'pointer-events: none; opacity: 0.5;' : '' ?>">

        <h3>Associar Pessoas à Matéria</h3>

        <fieldset>
            <legend>Cadastro de Matéria de Pessoas</legend>
            <!-- Campo para buscar o Policial Militar -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="policial_militar" class="form-label">Policial Militar</label>
                    <input type="text" class="form-control" id="buscaPolicial" name="buscaPolicial" autocomplete="on" placeholder="Digite o nome do policial">

                </div>

                <!-- Campo para o Posto/Graduação -->
                <div class="col-md-4">
                    <label for="postoGraduacao" class="form-label">Posto/Graduação</label>
                    <select class="form-select" id="postoGraduacao" name="postoGraduacao" disabled>
                        <option value="">Selecione</option>
                    </select>
                </div>


                <div class="col-md-4">
                    <label for="unidade" class="form-label">Unidade</label>
                    <select class="form-select" id="unidade" name="unidade" disabled>
                        <option value="">Selecione</option>
                    </select>
                </div>


                <!-- férias -->
                <div id="campoFerias" style="display: none; border: 1px solid #ccc; padding: 10px; margin-top: 20px;">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="dataInicial" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="dataInicial" name="dataInicial">
                        </div>
                        <div class="col-md-4">
                            <label for="dataFinal" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="dataFinal" name="dataFinal">
                        </div>
                        <div class="col-md-4">
                            <label for="anoBase" class="form-label">Ano Base</label>
                            <input type="number" class="form-control" id="anoBase" name="anoBase" min="2000" max="2100">
                        </div>
                    </div>
                </div>

            </div>
            <div class="row mb-3">
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" id="btnAdicionarPM" style="margin-right: 15px;">Adicionar PM</button>
                    <button type="reset" class="btn btn-secondary">Cancelar</button>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Lista de Matérias de Pessoas</legend>
            <table class="table table-bordered" id="tabelaPessoas">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Posto/Graduação</th>
                        <th>Unidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pessoasAssociadas)): ?>
                        <?php foreach ($pessoasAssociadas as $pessoa): ?>
                            <tr data-matricula="<?= htmlspecialchars($pessoa['fk_poli_mili_matricula']) ?>">
                                <td><?= htmlspecialchars($pessoa['nome'] ?? 'N/D') ?></td>
                                <td><?= htmlspecialchars($pessoa['posto'] ?? 'N/D') ?></td>
                                <td><?= htmlspecialchars($pessoa['unidade'] ?? 'N/D') ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="excluirRegistro('<?= htmlspecialchars($pessoa['fk_poli_mili_matricula']) ?>')">Excluir</button>
                                    <button class="btn btn-warning btn-sm btnEditar">Editar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="nenhum-registro">
                            <td colspan="4" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </fieldset>
    </div>
    <!-- <script src="js/associar_pessoa_materia.js"></script> -->

</form> <!-- Mantém a estrutura do form corretamente -->