document.addEventListener('DOMContentLoaded', function () {
    const assuntoGeralSelect = document.getElementById('fk_assu_gera_cod');
    const campoFerias = document.getElementById('campoFerias');

    // Função para verificar e exibir/esconder os campos de férias
    function verificarAssuntoGeral() {
        const assuntoGeralCod = assuntoGeralSelect.value;
        if (assuntoGeralCod == '12') {
            campoFerias.style.display = 'block'; // Exibe os campos de férias
        } else {
            campoFerias.style.display = 'none'; // Esconde os campos de férias
        }
    }

    // Chamada inicial ao carregar a página
    verificarAssuntoGeral();

    // Adiciona o evento de mudança ao campo Assunto Geral
    assuntoGeralSelect.addEventListener('change', verificarAssuntoGeral);
});
