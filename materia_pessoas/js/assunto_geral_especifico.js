document.addEventListener('DOMContentLoaded', function () {
    const assuntoEspecificoSelect = document.getElementById('fk_assu_espe_cod');
    const assuntoGeralSelect = document.getElementById('fk_assu_gera_cod');
    const textoMateriaTextarea = document.getElementById('mate_bole_texto');
    const campoFerias = document.getElementById('campoFerias');

    // Função para verificar o valor do Assunto Geral
    function verificarAssuntoGeral() {
        const assuntoGeralCod = assuntoGeralSelect.value;
        console.log("Assunto Geral Selecionado:", assuntoGeralCod);

        // Se o Assunto Geral for 12 (Férias), mostrar os campos de férias
        if (assuntoGeralCod == '12') {
            campoFerias.style.display = 'block'; // Exibe os campos de férias
        } else {
            campoFerias.style.display = 'none'; // Esconde os campos de férias
        }
    }

    // Adicionando um evento de mudança no Assunto Específico
    assuntoEspecificoSelect.addEventListener('change', function () {
        const assuEspeCod = this.value;

        // Verificar se o Assunto Específico foi selecionado
        if (assuEspeCod) {
            fetch(`?action=fetch_assunto_texto&assu_espe_cod=${assuEspeCod}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log("Resposta do servidor:", data); // Verifique a resposta do servidor

                if (data.success) {
                    textoMateriaTextarea.value = data.assu_espe_texto || ''; // Atualiza o campo de texto

                    // Limpar todas as opções do Assunto Geral
                    assuntoGeralSelect.innerHTML = '';

                    // Adiciona a nova opção para Assunto Geral
                    if (data.assu_gera_cod && data.assu_gera_descricao) {
                        const option = document.createElement('option');
                        option.value = data.assu_gera_cod;
                        option.textContent = data.assu_gera_descricao;
                        option.selected = true; // Marcar como selecionado
                        assuntoGeralSelect.appendChild(option);

                        // Após a mudança, verifica o Assunto Geral
                        verificarAssuntoGeral();
                    } else {
                        // Adiciona uma opção padrão caso não haja dados
                        const defaultOption = document.createElement('option');
                        defaultOption.value = '';
                        defaultOption.textContent = 'Selecione o Assunto Geral';
                        assuntoGeralSelect.appendChild(defaultOption);
                    }
                } else {
                    alert('Erro ao buscar os dados do Assunto Específico.');
                }
            })
            .catch(error => console.error('Erro na requisição:', error));
        }
    });

    // Inicializa a visibilidade dos campos de férias com base no valor atual do Assunto Geral
    verificarAssuntoGeral(); // Chama a função para verificar se o Assunto Geral é Férias ao carregar a página
});
