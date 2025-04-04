<?php
require_once '../db.php';

function formatar_cpf($cpf)
{
    return preg_replace("/^(\d{3})(\d{3})(\d{3})(\d{2})$/", "$1.$2.$3-$4", $cpf);
}

$matricula = '';
$materias = [];
$dados_pessoais = [];
$imagem_url = '';
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'] ?? '';

    if (empty($matricula)) {
        $error_message = "Por favor, insira uma matrícula.";
    } else {
        try {
            // Ficha de alterações
            // Ficha de alterações
            $sql = '
SELECT
    mb.mate_bole_cod,
    mb.mate_bole_data,
    mb.mate_bole_data_doc,
    mb.mate_bole_texto,
    mb.mate_bole_nr_doc,
        td.tipo_docu_descricao,  

    td.tipo_docu_descricao,
    ag.assu_gera_cod,
    ag.assu_gera_descricao,
    ae.assu_espe_cod,
    ae.assu_espe_descricao
FROM bg.vw_pessoa_materia_detalhada vpm
INNER JOIN bg.materia_boletim mb ON vpm.materia_id = mb.mate_bole_cod
LEFT JOIN bg.tipo_documento td ON mb.fk_tipo_docu_cod = td.tipo_docu_cod
LEFT JOIN bg.assunto_geral ag ON mb.fk_assu_gera_cod = ag.assu_gera_cod
LEFT JOIN bg.assunto_especifico ae ON mb.fk_assu_espe_cod = ae.assu_espe_cod
WHERE vpm.matricula = :matricula
ORDER BY ag.assu_gera_descricao, ae.assu_espe_descricao;
';

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['matricula' => $matricula]);
            
            $materias = $stmt->fetchAll(PDO::FETCH_ASSOC); // Carrega todos os registros de uma vez

            $tipoDocumento = isset($materias[0]['tipo_docu_descricao']) ? $materias[0]['tipo_docu_descricao'] : 'Tipo de documento não encontrado';


            // ADICIONAR ESTE BLOCO ABAIXO:
            foreach ($materias as &$materia) { // Note o & comercial
                $materia['detalhes'] = [[
                    'texto' => $materia['mate_bole_texto'],
                    'data' => $materia['mate_bole_data'],
                    'documento' => $materia['mate_bole_nr_doc']
                ]];
            }
            unset($materia); // Limpar referência para evitar problemas 

            if (!empty($materias)) {
                $success_message = "Matérias encontradas:";
            } else {
                $error_message = "Não há matérias relacionadas à matrícula fornecida.";
            }



            // Dados pessoais
            $sql_pessoal = '
                SELECT
                    matricula,
                    nome,
                    cpf,
                    quadro,
                    pg_descricao,
                    comando,
                    unidade,
                    subunidade
                FROM bg.vw_policiais_militares
                WHERE matricula = :matricula
                LIMIT 1
            ';
            $stmt_pessoal = $pdo->prepare($sql_pessoal);
            $stmt_pessoal->execute(['matricula' => $matricula]);
            $dados_pessoais = $stmt_pessoal->fetch(PDO::FETCH_ASSOC);

            if (!$dados_pessoais) {
                $error_message .= "<br>Dados pessoais não encontrados.";
            }

            // Buscar imagem
            $sql_imagem = 'SELECT imagemurl FROM bg.vw_login_escala WHERE matricula = :matricula LIMIT 1';
            $stmt_img = $pdo->prepare($sql_imagem);
            $stmt_img->execute(['matricula' => $matricula]);
            $img_result = $stmt_img->fetch(PDO::FETCH_ASSOC);
            if ($img_result && !empty($img_result['imagemurl'])) {
                $imagem_url = $img_result['imagemurl'];
            }
        } catch (PDOException $e) {
            $error_message = "Erro ao buscar dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Consulta de Matérias</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .form-container {
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .tabs {
            margin-top: 30px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #ccc;
        }

        .tab-button {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f9f9f9;
            border: none;
            border-bottom: 3px solid transparent;
            font-weight: bold;
        }

        .tab-button.active {
            border-bottom: 3px solid #7ca92b;
            background-color: #fff;
        }

        .tab-content {
            display: none;
            padding: 20px 0;
        }

        .tab-content.active {
            display: block;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th,
        .custom-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .custom-table thead {
            background-color: #f2f2f2;
        }

        .print-buttons {
            margin: 20px 0;
        }

        .print-buttons button {
            margin-right: 10px;
            padding: 6px 12px;
            background-color: #7ca92b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .print-buttons button:hover {
            background-color: #688c22;
        }

        /* Estilo para a linha que mostra os detalhes */
        .custom-table tr.details-row {
            background-color: #f9f9f9;
            transition: all 0.3s ease;
        }

        .custom-table .details-container {
            padding: 10px;
            background: #eaf4e2;
            /* Cor suave para destacar os detalhes */
            border-left: 4px solid #7ca92b;
            margin-top: 10px;
            border-radius: 4px;
        }

        .custom-table .details-container ul {
            list-style-type: none;
            padding-left: 0;
        }

        .custom-table .details-container li {
            margin-bottom: 10px;
            line-height: 1.6;
        }

        /* Ícone de visualizar detalhes */
        .custom-table td:last-child {
            text-align: center;
            cursor: pointer;
            color: #7ca92b;
            font-weight: bold;
        }

        .custom-table td:last-child:hover {
            color: #4a773c;
        }

        /* Animação suave para mostrar e esconder detalhes */
        .details-row {
            display: none;
            transition: all 0.3s ease;
        }

        .details-container {
            opacity: 0;
            height: 0;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .details-container.show {
            opacity: 1;
            height: auto;
            padding: 10px;
        }

        /* Estilo para o ícone de seta */
        .toggle-icon {
            transition: transform 0.3s ease;
            display: inline-block;
            margin-left: 8px;
        }

        /* Quando os detalhes estão visíveis, a seta gira para baixo */
        .details-container.show~.toggle-icon {
            transform: rotate(90deg);
        }

        /* Animação suave para detalhes */
        .details-row {
            display: none;
            transition: all 0.3s ease;
        }

        .details-container {
            opacity: 0;
            height: 0;
            overflow: hidden;
            padding: 0;
            transition: all 0.4s ease;
        }

        .details-container.show {
            opacity: 1;
            height: auto;
            padding: 10px;
        }

        /* Estilo da seta animada */
        .toggle-icon {
            transition: transform 0.3s ease;
            display: inline-block;
            margin-left: 8px;
        }

        .rotate {
            transform: rotate(90deg);
        }

       /* Cabeçalho só aparece na impressão */
/* Cabeçalho só aparece na impressão */
/* Cabeçalho só aparece na impressão */
/* Cabeçalho só aparece na impressão */
/* Cabeçalho só aparece na impressão */
/* Cabeçalho só aparece na impressão */
.print-header {
    display: none;
}

/* Estilo da área de impressão */
@media print {
    body * {
        visibility: hidden;
    }

    .printable, .printable * {
        visibility: visible;
    }

    .printable {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* Exibe o cabeçalho apenas na primeira página */
    .print-header {
        display: block;
        position: relative;
        top: 0;
        width: 100%;
        margin: 0;
    }

    /* Remove quebra de página após o cabeçalho */
    .print-header + .content-start {
        page-break-before: auto;
    }
}








    </style>
</head>

<body>

<!-- Cabeçalho para impressão -->
<div id="print-header" class="print-header" style="text-align: center; margin-top: 20px;">
<img src="http://localhost/BOLETIM/images/download.png" alt="Brasão do Estado de Roraima" style="height: 100px;">    <h3>Governo do Estado de Roraima<br>
        Polícia Militar do Estado de Roraima<br>
        <em>"Amazônia: patrimônio dos brasileiros"</em>
    </h3>
    <!-- <p><strong><?php echo $tipoDocumento; ?></strong></p> -->
     <p><strong>FICHA INDIVIDUAL</strong></p>
</div>






    <div class="container">
        <div class="form-container">
            <h1>Consulta de Matérias</h1>
            <form method="POST">
                <label for="matricula">Matrícula:</label>
                <input type="text" id="matricula" name="matricula" value="<?= htmlspecialchars($matricula); ?>" required>
                <button type="submit">Consultar</button>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="message error"><?= $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="message success"><?= $success_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($dados_pessoais) || !empty($materias)): ?>
            <div class="tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" onclick="showTab('pessoais')">Dados Pessoais</button>
                    <button class="tab-button" onclick="showTab('ficha')">Ficha de Alterações</button>
                </div>

                <div class="print-buttons">
                    <button onclick="printSection('pessoais')">🖨️ Imprimir Dados Pessoais</button>
                    <button onclick="printSection('ficha')">🖨️ Imprimir Ficha de Alterações</button>
                    <button onclick="printAll()">🖨️ Imprimir Tudo</button>
                </div>

                <!-- DADOS PESSOAIS -->
                <div id="pessoais" class="tab-content active content-start">

                <div style="border: 1px solid #ccc; padding: 20px;">
                        <!-- TÍTULO DESTACADO -->
                        <div style="
            background-color: #eaf4e2;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #4a773c;
            border: 1px solid #c5e0b4;
            margin-bottom: 20px;
            text-transform: uppercase;
        ">
                            DADOS PESSOAIS
                        </div>

                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                            <!-- COLUNA 1 -->
                            <div style="flex: 1; min-width: 250px;">
                                <p><strong>Nome:</strong> <?= htmlspecialchars($dados_pessoais['nome']) ?></p>
                                <p><strong>Sexo:</strong> -</p>
                                <p><strong>Escolaridade:</strong> -</p>
                                <p><strong>Data de Nascimento:</strong> -</p>
                                <p><strong>Naturalidade:</strong></p>

                            </div>

                            <!-- COLUNA 2 -->
                            <div style="flex: 1; min-width: 250px;">
                                <p><strong>Estado Civil:</strong> -</p>
                                <p><strong>Religião:</strong> -</p>
                                <p><strong>Autodeclaração Étnico-:</strong> -</p>
                                <p><strong>Idade: </strong> -</p>
                                <p><strong>UF:</strong> -</p>
                            </div>

                            <!-- FOTO -->
                            <div style="width: 120px; text-align: center; margin-top: 10px;">
                                <?php if ($imagem_url): ?>
                                    <img src="<?= htmlspecialchars($imagem_url) ?>" alt="Foto" style="
            width: 100%;
            aspect-ratio: 3/4;
            object-fit: cover;
            max-height: 180px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        ">
                                <?php else: ?>
                                    <div style="
            width: 100%;
            height: 160px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #f5f5f5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #888;
            font-size: 12px;
        ">
                                        <div style="font-size: 28px; margin-bottom: 5px;">📷</div>
                                        Sem foto
                                    </div>
                                <?php endif; ?>
                            </div>


                        </div>


                        <!-- SEPARADOR + DOCUMENTOS -->
                        <hr style="margin: 25px 0;">
                        <h3 style="color: #4a773c;">Documentos</h3>
                        <p><strong>CPF:</strong> <?= htmlspecialchars(formatar_cpf($dados_pessoais['cpf'])) ?> &nbsp;
                            <strong>PIS/Pasep:</strong> - &nbsp; <strong>RA:</strong> -
                        </p>
                        <p><strong>Identidade Civil:</strong> - &nbsp; <strong>Órgão:</strong> - &nbsp; <strong>UF:</strong> -</p>

                        <!-- SEPARADOR ANTES DE DADOS MÉDICOS -->
                        <hr style="margin: 25px 0;">

                        <h3 style="color: #4a773c;">Dados Médicos</h3>
                        <p><strong>Tipo Sanguíneo:</strong> - &nbsp;
                            <strong>Fator RH:</strong> - &nbsp;
                            <strong>Doador Órgãos:</strong> ( )Sim &nbsp; ( )Não
                        </p>

                        <!-- TÍTULO DADOS FUNCIONAIS -->
                        <div style="
            background-color: #eaf4e2;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            color: #4a773c;
            border: 1px solid #c5e0b4;
            margin: 40px 0 20px;
            text-transform: uppercase;
        ">
                            DADOS FUNCIONAIS
                        </div>

                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                            <!-- COLUNA 1 -->
                            <div style="flex: 1 1 50%; min-width: 280px;">
                                <p><strong>Matrícula:</strong> <?= htmlspecialchars($dados_pessoais['matricula']) ?></p>
                                <p><strong>Quadro:</strong> <?= htmlspecialchars($dados_pessoais['quadro']) ?></p>
                                <p><strong>Posto/Graduação:</strong> <?= htmlspecialchars($dados_pessoais['pg_descricao']) ?></p>
                                <p><strong>Situação Funcional:</strong> -</p>
                                <p><strong>Tempo de Serviço:</strong> -</p> <!-- Novo campo adicionado -->
                            </div>

                            <!-- COLUNA 2 -->
                            <div style="flex: 1 1 50%; min-width: 280px;">
                                <p><strong>Comando:</strong> <?= htmlspecialchars($dados_pessoais['comando']) ?></p>
                                <p><strong>Unidade:</strong> <?= htmlspecialchars($dados_pessoais['unidade']) ?></p>
                                <p><strong>Subunidade (Lotação):</strong> <?= htmlspecialchars($dados_pessoais['subunidade']) ?></p>
                                <p><strong>Função Atual:</strong> -</p>
                                <p><strong>Comportamento:</strong> -</p>
                            </div>
                        </div>
                    </div>




                </div>






                <!-- FICHA DE ALTERAÇÕES -->
                <!-- FICHA DE ALTERAÇÕES -->
                <!-- FICHA DE ALTERAÇÕES -->
                <div id="ficha" class="tab-content">
                    <?php if (!empty($materias)): ?>
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Modalidade</th>
                                    <th>Assunto Geral</th>
                                    <th>Data de Início</th>
                                    <th>Data de Encerramento</th>
                                    <th>Documento</th>
                                    <th>Detalhes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($materias as $index => $materia): ?>
                                    <tr onclick="toggleDetails(<?= $index; ?>)" style="cursor: pointer;">
                                        <td><?= htmlspecialchars($materia['assu_espe_descricao']); ?></td>
                                        <td><?= htmlspecialchars($materia['assu_gera_descricao']); ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($materia['mate_bole_data']))); ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($materia['mate_bole_data_doc']))); ?></td>
                                        <td><?= 'BG Nº ' . htmlspecialchars($materia['mate_bole_nr_doc']); ?></td>
                                        <td>
                                            🔍 Ver Detalhes
                                            <span id="toggle-icon-<?= $index; ?>" class="toggle-icon">🔽</span>
                                        </td>
                                    </tr>
                                    <tr id="details-<?= $index; ?>" class="details-row">
                                        <td colspan="6">
                                            <div class="details-container">
                                                <strong>Detalhes da Modalidade:</strong>
                                                <ul>
                                                    <?php if (!empty($materia['detalhes'])): ?>
                                                        <?php foreach ($materia['detalhes'] as $detalhe): ?>
                                                            <li>
                                                                <strong>Texto:</strong> <?= htmlspecialchars($detalhe['texto']); ?> <br>
                                                                <strong>Data:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($detalhe['data']))); ?> <br>
                                                                <strong>Documento:</strong> <?= htmlspecialchars($detalhe['documento']); ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <li>Nenhum detalhe encontrado para esta matéria.</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>



                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Nenhuma alteração encontrada.</p>
                    <?php endif; ?>
                </div>

            </div>

        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(index) {
            const row = document.getElementById('details-' + index);
            const container = row.querySelector('.details-container');
            const icon = document.getElementById('toggle-icon-' + index);

            if (container.classList.contains('show')) {
                // Esconder com deslizamento suave
                container.style.height = container.scrollHeight + 'px'; // Define a altura atual
                requestAnimationFrame(() => {
                    container.style.height = '0';
                    container.style.padding = '0';
                    container.style.opacity = '0';
                });

                icon.classList.remove('rotate');

                setTimeout(() => {
                    container.classList.remove('show');
                    row.style.display = 'none';
                }, 400);
            } else {
                // Mostrar com deslizamento suave
                row.style.display = 'table-row';
                container.style.height = '0';
                container.style.padding = '0';
                container.style.opacity = '0';

                requestAnimationFrame(() => {
                    container.classList.add('show');
                    container.style.height = container.scrollHeight + 'px';
                    container.style.padding = '10px';
                    container.style.opacity = '1';
                });

                icon.classList.add('rotate');

                setTimeout(() => {
                    container.style.height = 'auto'; // Permitir crescimento dinâmico se necessário
                }, 400);
            }
        }





        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab-content');
            const buttons = document.querySelectorAll('.tab-button');
            tabs.forEach(tab => tab.classList.remove('active'));
            buttons.forEach(btn => btn.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function printSection(tabId) {
    const section = document.getElementById(tabId);
    const header = document.getElementById('print-header').cloneNode(true);
    
    const printableArea = document.createElement('div');
    printableArea.className = 'printable';
    printableArea.appendChild(header);
    printableArea.appendChild(section.cloneNode(true));
    
    document.body.appendChild(printableArea);
    window.print();
    document.body.removeChild(printableArea);
}

        function cloneAndFixVisibility(id) {
            const clone = document.getElementById(id).cloneNode(true);
            clone.classList.remove('tab-content');
            clone.style.display = 'block';
            return clone;
        }

        function printAll() {
    const header = document.getElementById('print-header').cloneNode(true);
    const dados = document.getElementById('pessoais').cloneNode(true);
    const ficha = document.getElementById('ficha').cloneNode(true);

    const allContent = document.createElement('div');
    allContent.className = 'printable';

    allContent.appendChild(header);
    allContent.appendChild(dados);
    allContent.appendChild(ficha);

    document.body.appendChild(allContent);
    window.print();
    document.body.removeChild(allContent);
}

    </script>
</body>

</html>