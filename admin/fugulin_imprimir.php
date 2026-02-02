<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    die("Acesso negado.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die("ID inválido.");
}

// Busca dados da classificação
$stmt = $pdo->prepare("
    SELECT c.*, u.nome as profissional, s.nome as setor, l.descricao as leito
    FROM fugulin_classificacoes c
    JOIN usuarios u ON c.id_usuario = u.id
    JOIN fugulin_setores s ON c.id_setor = s.id
    JOIN fugulin_leitos l ON c.id_leito = l.id
    WHERE c.id = ?
");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    die("Registro não encontrado.");
}

// Busca respostas detalhadas
$stmt_resp = $pdo->prepare("
    SELECT r.*, q.titulo as questao, o.descricao as opcao
    FROM fugulin_respostas r
    JOIN fugulin_questoes q ON r.id_questao = q.id
    JOIN fugulin_opcoes o ON r.id_opcao = o.id
    WHERE r.id_classificacao = ?
    ORDER BY q.ordem ASC
");
$stmt_resp->execute([$id]);
$respostas = $stmt_resp->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Impressão Classificação Fugulin - <?php echo $c['paciente_nome']; ?></title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a202c;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            background: #fff;
        }
        .container {
            width: 100%;
            max-width: 100%;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #2d3748;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo img {
            max-height: 70px;
            width: auto;
        }
        .title {
            text-align: right;
        }
        .title h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
            color: #2d3748;
        }
        .title p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #718096;
            font-weight: bold;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 8px 12px;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .info-item strong {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #a0aec0;
            letter-spacing: 0.5px;
        }
        .info-item span {
            font-size: 13px;
            font-weight: bold;
            color: #2d3748;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .results-table th, .results-table td {
            text-align: left;
            padding: 8px 10px;
            border-bottom: 1px solid #edf2f7;
        }
        .results-table th {
            background: #edf2f7;
            font-size: 10px;
            text-transform: uppercase;
            color: #4a5568;
            letter-spacing: 0.5px;
        }
        .results-table td {
            font-size: 12px;
        }
        .summary-box {
            background: #2d3748;
            color: #fff;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            page-break-inside: avoid;
        }
        .summary-box .score-label {
            font-size: 10px;
            text-transform: uppercase;
            opacity: 0.8;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .summary-box .score-value {
            font-size: 32px;
            font-weight: 900;
            line-height: 1;
        }
        .summary-box .cls-value {
            text-align: right;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #edf2f7;
            font-size: 10px;
            color: #a0aec0;
            text-align: center;
        }
        .btn-print {
            margin-top: 20px;
            text-align: center;
        }
        .btn-print button {
            padding: 12px 40px;
            background: #3182ce;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background 0.2s;
        }
        .btn-print button:hover {
            background: #2b6cb0;
        }
        @media print {
            body { padding: 0; }
            .btn-print { display: none; }
            .info-item { background: #f7fafc !important; -webkit-print-color-adjust: exact; }
            .summary-box { background: #2d3748 !important; -webkit-print-color-adjust: exact; }
            .results-table th { background: #edf2f7 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="../images/hse.png" alt="Logo HSE">
            </div>
            <div class="title">
                <h1>Classificação de Paciente</h1>
                <p>Escala de Fugulin</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item" style="grid-column: span 3;">
                <strong>Paciente</strong>
                <span><?php echo cleanInput($c['paciente_nome']); ?></span>
            </div>
            <div class="info-item">
                <strong>Setor / Leito</strong>
                <span><?php echo $c['setor']; ?> - <?php echo $c['leito']; ?></span>
            </div>
            <div class="info-item">
                <strong>Enfermeiro(a)</strong>
                <span><?php echo $c['profissional']; ?></span>
            </div>
            <div class="info-item">
                <strong>Data/Hora</strong>
                <span><?php echo date('d/m/Y H:i', strtotime($c['data_registro'])); ?></span>
            </div>
        </div>

        <table class="results-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Indicador de Cuidado</th>
                    <th style="width: 50%;">Nível Selecionado</th>
                    <th style="width: 10%; text-align: center;">Pts</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($respostas as $r): ?>
                <tr>
                    <td style="font-weight: bold; color: #4a5568;"><?php echo $r['questao']; ?></td>
                    <td><?php echo $r['opcao']; ?></td>
                    <td style="text-align: center; font-weight: bold;"><?php echo $r['pontos']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-box">
            <div>
                <div class="score-label">Total de Pontuação</div>
                <div class="score-value"><?php echo $c['total_pontos']; ?></div>
            </div>
            <div class="cls-value">
                <?php echo $c['classificacao']; ?>
            </div>
        </div>

        <div class="footer">
            Relatório gerado em <?php echo date('d/m/Y \à\s H:i'); ?> • Sistema HospSys
            <br>
            <strong>Hospital Santo Expedito</strong> • Compromisso com a Vida
        </div>

        <div class="btn-print">
            <button onclick="window.print()">Imprimir Registro</button>
        </div>
    </div>

    <script>
        // Auto-print se for mobile ou se o usuário preferir
        // window.onload = () => { window.print(); }
    </script>
</body>
</html>
