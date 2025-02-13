$(document).ready(function () {
    // 1. Obtém o valor da matéria (usado para envio na busca, se necessário)
    // Se o input hidden com id "mate_bole_cod" existir, pegue seu valor.
    let mateBoleCod = $("#mate_bole_cod").val();  // Pode ser usado se precisar enviar esse dado no autocomplete

    /*******************************************
     * AUTOCOMPLETE: Busca do Policial Militar *
     *******************************************/
    $("#buscaPolicial").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/Boletim/materia_pessoas/includes/user_functions.php",
                method: "POST",
                data: {
                    term: request.term,
                    action: 'buscar_policial_militar'
                    // Se desejar enviar mateBoleCod na requisição, descomente a linha abaixo:
                    // , mate_bole_cod: mateBoleCod
                },
                success: function (data) {
                    try {
                        // Se a resposta for string, faz o parse para JSON
                        const jsonData = (typeof data === "string") ? JSON.parse(data) : data;
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
            // Quando o usuário seleciona um policial, armazena o objeto no input
            $("#buscaPolicial")
                .val(ui.item.label)
                .data("selected-policial", ui.item);

            // Preenche o select de posto/graduação com o ID numérico e exibe a descrição
            $("#postoGraduacao")
                .html(`<option value="${ui.item.postoGradCod}" selected>${ui.item.pg_descricao}</option>`)
                .prop("disabled", false);

            // Preenche o select de unidade (pode ser texto ou um ID, conforme sua necessidade)
            $("#unidade")
                .html(`<option value="${ui.item.unidade}" selected>${ui.item.unidade}</option>`)
                .prop("disabled", false);
        }
    });

    /******************************************
     * Botão "Adicionar PM" - Envio via AJAX  *
     ******************************************/
    $("#btnAdicionarPM").click(function () {
        // Obtém o objeto selecionado no autocomplete
        const policial = $("#buscaPolicial").data("selected-policial");
        if (!policial) {
            alert("Selecione um policial no autocomplete.");
            return;
        }
        const matriculaPM = policial.value;
        const nomePM = policial.label;
        
        // Captura o valor numérico e o texto para o posto/graduação
        const postoGraduacaoId = $("#postoGraduacao option:selected").val();
        const postoGraduacaoTexto = $("#postoGraduacao option:selected").text();
        
        const unidade = $("#unidade option:selected").val();
        const dataInicio = $("#dataInicial").val();
        const dataFim = $("#dataFinal").val();
        const anoBase = $("#anoBase").val();
        const mateBoleCod = $("#mate_bole_cod").val();
    
        if (!mateBoleCod) {
            alert("É necessário ter uma matéria salva para associar pessoas.");
            return;
        }
    
        // Envio via AJAX usando o valor numérico para "postoGraduacao"
        $.ajax({
            url: "/Boletim/materia_pessoas/includes/salvar_pessoa_materia.php",
            method: "POST",
            data: {
                mate_bole_cod: mateBoleCod,
                matriculaPM: matriculaPM,
                postoGraduacao: postoGraduacaoId,  // Envia o ID para o banco
                unidade: unidade,
                dataInicio: dataInicio,
                dataFim: dataFim,
                anoBase: anoBase
            },
            dataType: "json",
            success: function (resposta) {
                if (resposta.success) {
                    alert(resposta.mensagem);
                    // Ao inserir na tabela, use o texto (descrição) para o posto/graduação
                    inserirLinhaNaTabela(matriculaPM, nomePM, postoGraduacaoTexto, unidade);
                    // Limpa os campos após inserir
                    $("#buscaPolicial").val("").removeData("selected-policial");
                    $("#postoGraduacao").prop("disabled", true).html('<option value="">Selecione</option>');
                    $("#unidade").prop("disabled", true).html('<option value="">Selecione</option>');
                    $("#dataInicial").val("");
                    $("#dataFim").val("");
                    $("#anoBase").val("");
                } else {
                    alert(resposta.mensagem || "Falha ao inserir registro.");
                }
            },
            error: function (xhr, status, error) {
                console.error("Erro na requisição:", error);
                alert("Ocorreu um erro ao tentar salvar.");
            }
        });
    });
    
    
    /******************************************
     * Função para Inserir Linha na Tabela   *
     ******************************************/
    function inserirLinhaNaTabela(matricula, nome, posto, unidade) {
        const tabela = $("#tabelaPessoas tbody");
        // Evita duplicidade: se o policial já foi adicionado, alerta e retorna
        if (tabela.find(`tr[data-matricula="${matricula}"]`).length > 0) {
            alert("Este policial já foi adicionado.");
            return;
        }
        tabela.find(".nenhum-registro").remove();
        const novaLinha = `
            <tr data-matricula="${matricula}">
                <td>${nome}</td>
                <td>${posto}</td>
                <td>${unidade}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${matricula}')">Excluir</button>
                    <button class="btn btn-warning btn-sm btnEditar">Editar</button>
                </td>
            </tr>
        `;
        tabela.append(novaLinha);
    }

    /******************************************
     * Funções para Excluir e Editar Registros *
     ******************************************/
    window.excluirRegistro = function (matr) {
        const tabela = $("#tabelaPessoas tbody");
        tabela.find(`tr[data-matricula="${matr}"]`).remove();
        if (tabela.find("tr").length === 0) {
            tabela.append('<tr class="nenhum-registro"><td colspan="4" class="text-center">Nenhum registro encontrado.</td></tr>');
        }
    };

    $("#tabelaPessoas").on("click", ".btnEditar", function () {
        const linha = $(this).closest("tr");
        const postoAtual = linha.find("td:nth-child(2)").text();
        const unidadeAtual = linha.find("td:nth-child(3)").text();
        linha.find("td:nth-child(2)").html(`<input type="text" class="form-control" value="${postoAtual}" />`);
        linha.find("td:nth-child(3)").html(`<input type="text" class="form-control" value="${unidadeAtual}" />`);
        linha.find("td:nth-child(4)").html(`
            <button class="btn btn-success btn-sm btnSalvar">Salvar</button>
            <button class="btn btn-secondary btn-sm btnCancelar">Cancelar</button>
        `);
    });

    $("#tabelaPessoas").on("click", ".btnSalvar", function () {
        const linha = $(this).closest("tr");
        const novoPosto = linha.find("td:nth-child(2) input").val();
        const novaUnidade = linha.find("td:nth-child(3) input").val();
        linha.find("td:nth-child(2)").text(novoPosto);
        linha.find("td:nth-child(3)").text(novaUnidade);
        linha.find("td:nth-child(4)").html(`
            <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${linha.data("matricula")}')">Excluir</button>
            <button class="btn btn-warning btn-sm btnEditar">Editar</button>
        `);
    });

    $("#tabelaPessoas").on("click", ".btnCancelar", function () {
        const linha = $(this).closest("tr");
        const postoDigitado = linha.find("td:nth-child(2) input").val();
        const unidadeDigitada = linha.find("td:nth-child(3) input").val();
        linha.find("td:nth-child(2)").text(postoDigitado);
        linha.find("td:nth-child(3)").text(unidadeDigitada);
        linha.find("td:nth-child(4)").html(`
            <button class="btn btn-danger btn-sm" onclick="excluirRegistro('${linha.data("matricula")}')">Excluir</button>
            <button class="btn btn-warning btn-sm btnEditar">Editar</button>
        `);
    });
});
