document.addEventListener('DOMContentLoaded', function () {
    const btnAdicionarPM = document.getElementById('btnAdicionarPM');

    btnAdicionarPM.addEventListener('click', function () {
        const nomePolicial = document.getElementById('buscaPolicial').value;
        const postoGraduacao = document.getElementById('postoGraduacao').value;
        const unidade = document.getElementById('unidade').value;
        const mateBoleCod = document.querySelector('input[name="mate_bole_cod"]').value;
        const dataInicial = document.getElementById('dataInicial').value || '2000-01-01';
        const dataFinal = document.getElementById('dataFinal').value || '2000-01-01';
        const anoBase = document.getElementById('anoBase').value || 0;

        if (!nomePolicial || !postoGraduacao || !unidade || !mateBoleCod) {
            alert("Preencha todos os campos obrigatórios!");
            return;
        }

        const formData = new FormData();
        formData.append('matricula', nomePolicial);
        formData.append('postoGraduacao', postoGraduacao);
        formData.append('unidade', unidade);
        formData.append('dataInicial', dataInicial);
        formData.append('dataFinal', dataFinal);
        formData.append('anoBase', anoBase);
        formData.append('mate_bole_cod', mateBoleCod);

        fetch('includes/cadastro_policiais.php', {
            method: 'POST',
            body: formData
        })

            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Policial adicionado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro ao adicionar policial: ' + data.error);
                }
            })
            .catch(error => console.error('Erro na requisição:', error));
    });
});
