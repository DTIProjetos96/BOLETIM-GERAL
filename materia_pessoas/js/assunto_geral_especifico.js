document.addEventListener('DOMContentLoaded', function () {
    const assuntoEspecificoSelect = document.getElementById('fk_assu_espe_cod');
    const assuntoGeralSelect = document.getElementById('fk_assu_gera_cod');
    const campoFerias = document.getElementById('campoFerias');

    function verificarAssuntoGeral() {
        const assuntoGeralCod = assuntoGeralSelect.value;
        console.log("Assunto Geral Selecionado:", assuntoGeralCod);

        if (assuntoGeralCod === '12') {
            campoFerias.style.display = 'block';
            sessionStorage.setItem('assuntoGeral', '12'); // Salva no sessionStorage
        } else {
            campoFerias.style.display = 'none';
            sessionStorage.removeItem('assuntoGeral'); // Remove caso não seja "Férias"
        }
    }

    // Função para carregar o Assunto Geral ao abrir a tela de edição
    function carregarAssuntoGeral() {
        const assuEspeCod = assuntoEspecificoSelect.value;

        if (assuEspeCod) {
            fetch(`?action=fetch_assunto_texto&assu_espe_cod=${assuEspeCod}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                console.log("Resposta do servidor:", data);

                if (data.success) {
                    document.getElementById('mate_bole_texto').value = data.assu_espe_texto || '';

                    // Limpa o select antes de adicionar a opção correta
                    assuntoGeralSelect.innerHTML = '';

                    if (data.assu_gera_cod && data.assu_gera_descricao) {
                        const option = document.createElement('option');
                        option.value = data.assu_gera_cod;
                        option.textContent = data.assu_gera_descricao;
                        option.selected = true;
                        assuntoGeralSelect.appendChild(option);

                        // **Força o valor correto antes de acionar o evento `change`**
                        assuntoGeralSelect.value = data.assu_gera_cod;
                        sessionStorage.setItem('assuntoGeral', data.assu_gera_cod); // Salva o valor

                        // Aguarda a atualização do DOM antes de verificar
                        setTimeout(() => {
                            verificarAssuntoGeral();
                            assuntoGeralSelect.dispatchEvent(new Event('change')); // Força atualização
                        }, 100);
                    } else {
                        sessionStorage.removeItem('assuntoGeral');
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
    }

    // Verifica se já há um Assunto Geral salvo na edição
    if (assuntoEspecificoSelect.value) {
        setTimeout(carregarAssuntoGeral, 500); // Aguarda carregamento do DOM
    }

    // Adiciona eventos para alteração
    assuntoGeralSelect.addEventListener('change', verificarAssuntoGeral);
    assuntoEspecificoSelect.addEventListener('change', carregarAssuntoGeral);
});
