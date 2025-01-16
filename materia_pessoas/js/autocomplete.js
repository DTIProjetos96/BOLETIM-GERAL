$(document).ready(function () {
    // Configuração do autocomplete
    $("#buscaPolicial").off('select').autocomplete({
        source: function (request, response) {
            console.log("Termo enviado:", request.term); // Verifica o termo enviado
            $.ajax({
                url: "/Boletim/materia_pessoas/includes/user_functions.php",
                method: "POST",
                data: {
                    term: request.term,
                    action: 'buscar_policial_militar'
                },
                success: function (data) {
                    try {
                        console.log("Resposta do servidor:", data); // Verifica a resposta do backend
                        const jsonData = typeof data === "string" ? JSON.parse(data) : data;
                        response(jsonData);
                    } catch (e) {
                        console.error("Erro ao processar o JSON:", e);
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
            console.log("Item selecionado:", ui.item); // Verifica o item selecionado

            // Preenche os campos com os valores retornados
            $('#buscaPolicial').val(ui.item.label); // Nome do policial
            $('#postoGraduacao').html(
                `<option value="${ui.item.pg_descricao}" selected>${ui.item.pg_descricao}</option>`
            ).prop('disabled', false);
            $('#unidade').html(
                `<option value="${ui.item.unidade}" selected>${ui.item.unidade}</option>`
            ).prop('disabled', false);

            // Salva os dados no campo de busca para uso posterior
            $('#buscaPolicial').data('selected-policial', ui.item);
        }
    });

    // Adicionar PM à tabela
    $('#btnAdicionarPM').click(function () {
        // Recupera os dados do policial selecionado
        const policial = $('#buscaPolicial').data('selected-policial'); 
        const postoGraduacao = $('#postoGraduacao option:selected').text();
        const unidade = $('#unidade option:selected').text();
    
        console.log('Policial:', policial);
        console.log('Posto/Graduação:', postoGraduacao);
        console.log('Unidade:', unidade);
    
        // Validação se todos os campos estão preenchidos corretamente
        if (!policial || !postoGraduacao || !unidade || postoGraduacao === 'Selecione' || unidade === 'Selecione') {
            alert('Preencha todos os campos antes de adicionar.');
            return; // Interrompe a execução se houver erro
        }
    
        // Verifica se o policial já foi adicionado na tabela
        const tabela = $('#tabelaPessoas tbody');
        const jaAdicionado = tabela.find(`tr[data-matricula="${policial.value}"]`).length > 0;
    
        if (jaAdicionado) {
            alert('Este policial já foi adicionado.');
            return; // Interrompe a execução se já foi adicionado
        }
    
        // Remove a linha "Nenhum registro encontrado"
        tabela.find('.nenhum-registro').remove();
    
        // Adiciona a nova linha na tabela
        tabela.append(`
            <tr data-matricula="${policial.value}">
                <td>
                    <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${policial.value}')">Excluir</button>
                </td>
                <td>${policial.label}</td>
                <td>${postoGraduacao}</td>
                <td>${unidade}</td>
            </tr>
        `);
    
        // Limpa os campos após adicionar
        $('#buscaPolicial').val('');
        $('#buscaPolicial').removeData('selected-policial');
        $('#postoGraduacao').prop('disabled', true).html('<option value="">Selecione</option>');
        $('#unidade').prop('disabled', true).html('<option value="">Selecione</option>');
    });

    // Função para excluir um registro
    function excluirRegistro(matricula) {
        const tabela = $('#tabelaPessoas tbody');
        tabela.find(`tr[data-matricula="${matricula}"]`).remove();

        // Adiciona "Nenhum registro encontrado" se a tabela estiver vazia
        if (tabela.find('tr').length === 0) {
            tabela.append('<tr class="nenhum-registro"><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
        }
    }



    // Listener para botões de edição
    $('#tabelaPessoas').on('click', '.btnEditar', function () {
        var linha = $(this).closest('tr');

        // Obter os valores atuais
        var postoAtual = linha.find('td:nth-child(3)').text();
        var unidadeAtual = linha.find('td:nth-child(4)').text();

        // Substituir os textos por inputs
        linha.find('td:nth-child(3)').html(`
            <input type="text" class="form-control" value="${postoAtual}" />
        `);
        linha.find('td:nth-child(4)').html(`
            <input type="text" class="form-control" value="${unidadeAtual}" />
        `);

        // Alterar os botões de ação
        linha.find('td:first-child').html(`
            <button class="btn btn-success btn-sm btnSalvar">Salvar</button>
            <button class="btn btn-secondary btn-sm btnCancelar">Cancelar</button>
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
            <button class="btn btn-warning btn-sm btnEditar">Editar</button>
        `);
    });

    // Listener para botões de cancelar
    $('#tabelaPessoas').on('click', '.btnCancelar', function () {
        var linha = $(this).closest('tr');

        linha.find('td:nth-child(3)').text(linha.find('td:nth-child(3) input').val());
        linha.find('td:nth-child(4)').text(linha.find('td:nth-child(4) input').val());

        linha.find('td:first-child').html(`
            <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${linha.data('matricula')}')">Excluir</button>
            <button class="btn btn-warning btn-sm btnEditar">Editar</button>
        `);
    });
}); // Fim do document ready
