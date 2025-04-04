<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Individual do Policial Militar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 210mm;
            height: 297mm;
            margin: auto;
            padding: 20mm;
            border: 1px solid #000;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
        }

        .section {
            margin-bottom: 20px;
        }

        .label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Ficha Individual do Policial Militar</h2>

        <div class="section">
            <h3>Dados Pessoais</h3>
            <table>

                <tr>
                    <td class="label">Nome:</td>
                    <td>{{ poli_mili_nome }}</td>
                </tr>
                <tr>
                    <td class="label">Matrícula:</td>
                    <td>{{ poli_mili_matricula }}</td>
                </tr>
                <tr>
                    <td class="label">Matrícula SIAPE:</td>
                    <td>{{ poli_mili_matricula_siape }}</td>
                </tr>

                <tr>
                    <td class="label">Nome de Guerra:</td>
                    <td>{{ poli_mili_nome_guerra }}</td>
                </tr>
                <tr>
                    <td class="label">CPF:</td>
                    <td>{{ poli_mili_cpf }}</td>
                </tr>
                <tr>
                    <td class="label">Tipo Sanguíneo:</td>
                    <td>{{ fk_ts_frh_sigla }}</td>
                </tr>
                <tr>
                    <td class="label">Data de Nascimento:</td>
                    <td>{{ poli_mili_data_nascimento }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h3>Dados Funcionais</h3>
            <table>
                <tr>
                    <td class="label">Data de Incorporação:</td>
                    <td>{{ poli_mili_data_incorporacao }}</td>
                </tr>
                <tr>
                    <td class="label">Estado de Nascimento:</td>
                    <td>{{ fk_uf_nasc_cod }}</td>
                </tr>
                <tr>
                    <td class="label">Cidade de Nascimento:</td>
                    <td>{{ fk_cida_nasc_cod }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h3>Dados Individuais</h3>
            <table>
                <tr>
                    <td class="label">Data de Incorporação:</td>
                    <td>{{ poli_mili_data_incorporacao }}</td>
                </tr>
                <tr>
                    <td class="label">Estado de Nascimento:</td>
                    <td>{{ fk_uf_nasc_cod }}</td>
                </tr>
                <tr>
                    <td class="label">Cidade de Nascimento:</td>
                    <td>{{ fk_cida_nasc_cod }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>