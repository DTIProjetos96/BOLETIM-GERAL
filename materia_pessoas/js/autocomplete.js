$(document).ready(function () {
    // Configuração do autocomplete
    $("#buscaPolicial").off('select').autocomplete({
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
                        const jsonData = typeof data === "string" ? JSON.parse(data) : data;
                        response(jsonData);
                    } catch (e) {
                        alert("Erro ao processar os dados retornados.");
                    }
                },
                error: function () {
                    alert("Erro ao buscar policiais.");
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            // Preenche os campos com os valores retornados
            $('#buscaPolicial')
                .val(ui.item.label)
                .data('selected-policial', ui.item);

            $('#postoGraduacao')
                .html(`<option value="${ui.item.pg_descricao}" selected>${ui.item.pg_descricao}</option>`)
                .prop('disabled', false);

            $('#unidade')
                .html(`<option value="${ui.item.unidade}" selected>${ui.item.unidade}</option>`)
                .prop('disabled', false);
        }
    });

    // Adicionar PM à tabela
    $('#btnAdicionarPM').click(function () {
        const policial = $('#buscaPolicial').data('selected-policial');
        const postoGraduacao = $('#postoGraduacao option:selected').text();
        const unidade = $('#unidade option:selected').text();

        if (!policial || !postoGraduacao || !unidade || postoGraduacao === 'Selecione' || unidade === 'Selecione') {
            alert('Preencha todos os campos antes de adicionar.');
            return;
        }

        const tabela = $('#tabelaPessoas tbody');
        const jaAdicionado = tabela.find(`tr[data-matricula="${policial.value}"]`).length > 0;
        if (jaAdicionado) {
            alert('Este policial já foi adicionado.');
            return;
        }

        // Remove a linha "Nenhum registro encontrado" se existir
        tabela.find('.nenhum-registro').remove();

        // Cria a nova linha com Excluir + Editar
        tabela.append(`
            <tr data-matricula="${policial.value}">
                <td>${policial.label}</td>              
                <td>${postoGraduacao}</td>              
                <td>${unidade}</td>                     
                <td>
  <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${policial.value}')">Excluir</button>
  <button class="btn btn-warning btn-sm btnEditar">Editar</button>
</td>
            </tr>
        `);

        // Limpa os campos
        $('#buscaPolicial').val('').removeData('selected-policial');
        $('#postoGraduacao').prop('disabled', true).html('<option value="">Selecione</option>');
        $('#unidade').prop('disabled', true).html('<option value="">Selecione</option>');
    });

    // Função para excluir um registro
    window.excluirRegistro = function (matricula) {
        const tabela = $('#tabelaPessoas tbody');
        tabela.find(`tr[data-matricula="${matricula}"]`).remove();

        // Se ficar vazio, exibe “Nenhum registro”
        if (tabela.find('tr').length === 0) {
            tabela.append('<tr class="nenhum-registro"><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
        }
    };

    // EDITAR
    $('#tabelaPessoas').on('click', '.btnEditar', function () {
        const linha = $(this).closest('tr');

        // ler valores das colunas (2 e 3)
        const postoAtual = linha.find('td:nth-child(2)').text();
        const unidadeAtual = linha.find('td:nth-child(3)').text();

        // Substituir por inputs
        linha.find('td:nth-child(2)').html(`<input type="text" class="form-control" value="${postoAtual}" />`);
        linha.find('td:nth-child(3)').html(`<input type="text" class="form-control" value="${unidadeAtual}" />`);

        // Troca botões da 4ª coluna
        linha.find('td:nth-child(4)').html(`
            <button class="btn btn-success btn-sm btnSalvar">Salvar</button>
            <button class="btn btn-secondary btn-sm btnCancelar">Cancelar</button>
        `);
    });

    // SALVAR
    $('#tabelaPessoas').on('click', '.btnSalvar', function () {
        const linha = $(this).closest('tr');

        const novoPosto = linha.find('td:nth-child(2) input').val();
        const novaUnidade = linha.find('td:nth-child(3) input').val();

        // Atualiza as colunas
        linha.find('td:nth-child(2)').text(novoPosto);
        linha.find('td:nth-child(3)').text(novaUnidade);

        // Restaura os botões de ação
        linha.find('td:nth-child(4)').html(`
            <button class="btn btn-danger btn-sm" 
                    onclick="excluirRegistro('${linha.data('matricula')}')">
                Excluir
            </button>
            <button class="btn btn-warning btn-sm btnEditar">
                Editar
            </button>
        `);
    });

    // CANCELAR
    $('#tabelaPessoas').on('click', '.btnCancelar', function () {
        const linha = $(this).closest('tr');

        // Se quiser voltar aos valores anteriores antes de editar,
        // teria que ter guardado o valor inicial ou recarregar do BD.
        // Aqui, “Cancelar” apenas pega o que foi digitado e exibe (igual Salvar).
        const postoDigitado = linha.find('td:nth-child(2) input').val();
        const unidadeDigitada = linha.find('td:nth-child(3) input').val();

        linha.find('td:nth-child(2)').text(postoDigitado);
        linha.find('td:nth-child(3)').text(unidadeDigitada);

        // Volta botões de ação
        linha.find('td:nth-child(4)').html(`
            <button class="btn btn-danger btn-sm" 
                    onclick="excluirRegistro('${linha.data('matricula')}')">
                Excluir
            </button>
            <button class="btn btn-warning btn-sm btnEditar">
                Editar
            </button>
        `);
    });
});
