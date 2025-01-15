$(document).ready(function () {
    // Configuração do autocomplete
    $("#buscaPolicial").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/Boletim/materia_pessoas/includes/user_functions.php",
                method: "POST",
                data: {
                    term: request.term,
                    action: 'buscar_policial_militar'
                },
                success: function (data) {
                    try {
                        // Log detalhado da resposta para depuração
                        console.log("Resposta recebida do servidor:", data);
                
                        // Converte os dados para JSON se necessário
                        const jsonData = typeof data === "string" ? JSON.parse(data) : data;
                
                        // Certifica-se de que os dados estão no formato esperado
                        if (Array.isArray(jsonData) && jsonData.length > 0 && jsonData[0].label && jsonData[0].value) {
                            response(jsonData); // Passa os dados ao autocomplete
                        } else {
                            console.error("Formato inesperado dos dados recebidos:", jsonData);
                            alert("Erro: os dados recebidos não estão no formato esperado.");
                        }
                    } catch (e) {
                        console.error("Erro ao processar o JSON:", e);
                        console.error("Resposta recebida:", data);
                        alert("Erro ao processar os dados retornados.");
                    }
                },
                
                error: function (xhr, status, error) {
                    console.error("Erro na requisição AJAX:", error);
                    alert("Erro ao buscar policiais.");
                }
            });
        },
        minLength: 2, // Inicia a busca após 2 caracteres
        select: function (event, ui) {
            // Quando um item é selecionado, armazena os dados
            $('#buscaPolicial').data('selected-policial', ui.item);
        }
    });

    // Função para adicionar um policial à tabela
    $('#btnAdicionarPM').click(function () {
        var policial = $('#buscaPolicial').data('selected-policial');
        var unidade = $('#unidade').val();
        var postoGraduacao = $('#postoGraduacao').val();

        if (!policial || !unidade || !postoGraduacao) {
            alert('Preencha todos os campos antes de adicionar.');
            return;
        }

        // Verifica se a tabela está vazia
        var tabela = $('#tabelaPessoas tbody');
        if (tabela.find('tr').length === 1 && tabela.find('tr td').length === 1) {
            tabela.empty(); // Remove a linha "Nenhum registro encontrado."
        }

        // Verifica se o policial já foi adicionado
        if ($('tr[data-matricula="' + policial.value + '"]').length > 0) {
            alert('Este policial já foi adicionado.');
            return;
        }

        // Adiciona a nova linha na tabela
        var novaLinha = `
            <tr data-matricula="${policial.value}">
                <td>
                    <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${policial.value}')">Excluir</button>
                    <button class="btn btn-warning btn-sm btnEditar" style="margin-left: 5px;">Editar</button>
                </td>
                <td>${policial.label}</td>
                <td>${postoGraduacao}</td>
                <td>${unidade}</td>
            </tr>
        `;
        tabela.append(novaLinha);

        // Limpa os campos de entrada
        $('#buscaPolicial').val('');
        $('#buscaPolicial').removeData('selected-policial');
        $('#unidade').val('');
        $('#postoGraduacao').val('');
    });

    // Listener para botões de edição
    $('#tabelaPessoas').on('click', '.btnEditar', function () {
        var linha = $(this).closest('tr');
        var matricula = linha.data('matricula');

        // Evita múltiplas edições simultâneas
        if (linha.hasClass('editando')) {
            return;
        }
        linha.addClass('editando');

        // Obter os valores atuais
        var postoAtual = linha.find('td:nth-child(3)').text();
        var unidadeAtual = linha.find('td:nth-child(4)').text();

        // Substituir os textos por selects
        linha.find('td:nth-child(3)').html(`
            <input type="text" class="form-control" value="${postoAtual}" />
        `);
        linha.find('td:nth-child(4)').html(`
            <input type="text" class="form-control" value="${unidadeAtual}" />
        `);

        // Alterar os botões de ação
        linha.find('td:first-child').html(`
            <button class="btn btn-success btn-sm btnSalvar" style="margin-left: 5px;">Salvar</button>
            <button class="btn btn-secondary btn-sm btnCancelar" style="margin-left: 5px;">Cancelar</button>
        `);
    });

    // Listener para botões de salvar
    $('#tabelaPessoas').on('click', '.btnSalvar', function () {
        var linha = $(this).closest('tr');
        var novoPosto = linha.find('td:nth-child(3) input').val();
        var novaUnidade = linha.find('td:nth-child(4) input').val();

        // Atualiza os valores na tabela
        linha.find('td:nth-child(3)').text(novoPosto);
        linha.find('td:nth-child(4)').text(novaUnidade);

        // Restaura os botões de ação
        linha.find('td:first-child').html(`
            <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${linha.data('matricula')}')">Excluir</button>
            <button class="btn btn-warning btn-sm btnEditar" style="margin-left: 5px;">Editar</button>
        `);

        linha.removeClass('editando');
    });

    // Listener para botões de cancelar
    $('#tabelaPessoas').on('click', '.btnCancelar', function () {
        var linha = $(this).closest('tr');
        var postoAtual = linha.find('td:nth-child(3) input').val();
        var unidadeAtual = linha.find('td:nth-child(4) input').val();

        linha.find('td:nth-child(3)').text(postoAtual);
        linha.find('td:nth-child(4)').text(unidadeAtual);

        linha.find('td:first-child').html(`
            <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${linha.data('matricula')}')">Excluir</button>
            <button class="btn btn-warning btn-sm btnEditar" style="margin-left: 5px;">Editar</button>
        `);

        linha.removeClass('editando');
    });
});

// Função para excluir um registro
function excluirRegistro(matricula) {
    $('tr[data-matricula="' + matricula + '"]').remove();

    // Verifica se a tabela está vazia
    var tabela = $('#tabelaPessoas tbody');
    if (tabela.find('tr').length === 0) {
        tabela.append('<tr><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
    }
}
