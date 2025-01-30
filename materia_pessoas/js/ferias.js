document.addEventListener('DOMContentLoaded', function () {
    const campoFerias = document.getElementById('campoFerias');

    function verificarAssuntoGeral() {
        const assuntoGeralSelect = document.getElementById('fk_assu_gera_cod');
        if (!assuntoGeralSelect) {
            console.error("Elemento 'fk_assu_gera_cod' não encontrado.");
            return;
        }

        const assuntoGeralCod = assuntoGeralSelect.value || sessionStorage.getItem('assuntoGeral');
        console.log("Assunto Geral Selecionado:", assuntoGeralCod);

        if (assuntoGeralCod === '12') {
            campoFerias.style.display = 'block';
        } else {
            campoFerias.style.display = 'none';
        }
    }

    let tentativas = 0;
    const intervalo = setInterval(function () {
        const assuntoGeralSelect = document.getElementById('fk_assu_gera_cod');
        if (assuntoGeralSelect && assuntoGeralSelect.value !== "") {
            clearInterval(intervalo);
            verificarAssuntoGeral();
            assuntoGeralSelect.addEventListener('change', verificarAssuntoGeral);
        }
        if (++tentativas > 10) {
            clearInterval(intervalo);
        }
    }, 500);
});
