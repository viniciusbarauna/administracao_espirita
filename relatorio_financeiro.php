<?php
require 'auth.php';
require 'config.php';


$mes_atual = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano_atual = isset($_GET['ano']) ? $_GET['ano'] : date('Y');


$sqlFiltro = " WHERE MONTH(p.data_pagamento) = ? AND YEAR(p.data_pagamento) = ? AND p.substituido_por_id IS NULL";

try {
    
    $stmtTotal = $pdo->prepare("SELECT SUM(valor) as total FROM pagamentos p $sqlFiltro");
    $stmtTotal->execute([$mes_atual, $ano_atual]);
    $totalGeral = $stmtTotal->fetchColumn() ?: 0;

    
    $stmtMeios = $pdo->prepare("SELECT meio_pagamento, SUM(valor) as subtotal FROM pagamentos p $sqlFiltro GROUP BY meio_pagamento");
    $stmtMeios->execute([$mes_atual, $ano_atual]);
    $resumoMeios = $stmtMeios->fetchAll();

    
    $sqlLista = "SELECT p.*, m.nome as mensalista_nome, a.nome as admin_nome 
                 FROM pagamentos p 
                 JOIN mensalistas m ON p.mensalista_id = m.id
                 JOIN administradores a ON p.admin_id = a.id
                 $sqlFiltro 
                 ORDER BY p.data_pagamento ASC";
    $stmtLista = $pdo->prepare($sqlLista);
    $stmtLista->execute([$mes_atual, $ano_atual]);
    $lancamentos = $stmtLista->fetchAll();

} catch (PDOException $e) {
    die("Erro ao gerar relatório: " . $e->getMessage());
}

$meses_ano = [
    '01'=>'Janeiro', '02'=>'Fevereiro', '03'=>'Março', '04'=>'Abril',
    '05'=>'Maio', '06'=>'Junho', '07'=>'Julho', '08'=>'Agosto',
    '09'=>'Setembro', '10'=>'Outubro', '11'=>'Novembro', '12'=>'Dezembro'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório Financeiro - <?= $meses_ano[$mes_atual] ?>/<?= $ano_atual ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        .relatorio-header { display: none; }
        
        /* ESTILOS DE IMPRESSÃO */
        @media print {
            @page { size: A4; margin: 15mm; }
            body { background: white; font-size: 12pt; font-family: 'Times New Roman', serif; }
            .no-print, .navbar, .btn { display: none !important; } /* Esconde menus e botões */
            .relatorio-header { display: block; text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
            .card { border: 1px solid #ddd !important; box-shadow: none !important; }
            .bg-success, .bg-primary, .bg-warning { background-color: #eee !important; color: #000 !important; border: 1px solid #000; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000000 !important; padding: 5px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary no-print mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left"></i> Voltar</a>
        <span class="navbar-text">Módulo de Relatórios</span>
    </div>
</nav>

<div class="container bg-white p-5 shadow-sm rounded" style="min-height: 800px;">
    
    <div class="relatorio-header">
        <h2>LAR ESPÍRITA - DEMONSTRATIVO FINANCEIRO</h2>
        <p>Relatório de Fechamento de Caixa - Referência: <strong><?= $meses_ano[$mes_atual] ?> de <?= $ano_atual ?></strong></p>
        <p><small>Gerado em: <?= date('d/m/Y H:i') ?></small></p>
    </div>

    <div class="row mb-4 no-print align-items-end">
        <div class="col-md-8">
            <form class="row g-2">
                <div class="col-auto">
                    <label>Mês:</label>
                    <select name="mes" class="form-select">
                        <?php foreach($meses_ano as $num => $nome): ?>
                            <option value="<?= $num ?>" <?= $num == $mes_atual ? 'selected' : '' ?>><?= $nome ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label>Ano:</label>
                    <select name="ano" class="form-select">
                        <?php for($i=2024; $i<=date('Y'); $i++): ?>
                            <option value="<?= $i ?>" <?= $i == $ano_atual ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <button onclick="window.print()" class="btn btn-lg btn-success">
                <i class="fas fa-print me-2"></i> Imprimir Relatório
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-2">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">TOTAL ARRECADADO</h5>
                    <h2 class="display-4 fw-bold">R$ <?= number_format($totalGeral, 2, ',', '.') ?></h2>
                    <p><?= count($lancamentos) ?> doações registradas</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="card h-100 border-secondary">
                <div class="card-header fw-bold bg-light text-dark">Detalhamento por Meio</div>
                <ul class="list-group list-group-flush">
                    <?php foreach($resumoMeios as $rm): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= $rm['meio_pagamento'] ?>
                            <span class="badge bg-secondary rounded-pill" style="font-size: 1em;">
                                R$ <?= number_format($rm['subtotal'], 2, ',', '.') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <?php if(empty($resumoMeios)) echo "<li class='list-group-item text-muted'>Sem dados.</li>"; ?>
                </ul>
            </div>
        </div>
    </div>

    <hr>

    <h5 class="mb-3"><i class="fas fa-list"></i> Extrato Detalhado de Lançamentos</h5>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Data</th>
                    <th>Mensalista (Doador)</th>
                    <th>Ref.</th>
                    <th>Resp. (Admin)</th>
                    <th>Meio</th>
                    <th class="text-end">Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($lancamentos) > 0): ?>
                    <?php foreach($lancamentos as $l): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($l['data_pagamento'])) ?></td>
                        <td><?= htmlspecialchars($l['mensalista_nome']) ?></td>
                        <td><small><?= $l['referencia_periodo'] ?></small></td>
                        <td><small class="text-muted"><?= htmlspecialchars($l['admin_nome']) ?></small></td>
                        <td><?= $l['meio_pagamento'] ?></td>
                        <td class="text-end fw-bold">
                            <?= number_format($l['valor'], 2, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Nenhum pagamento encontrado neste período.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="5" class="text-end">TOTAL DO MÊS:</td>
                    <td class="text-end">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="relatorio-header mt-5 pt-5" style="border: none; margin-top: 50px;">
        <div class="row">
            <div class="col-6 text-center">
                __________________________________<br>
                Tesoureiro Responsável
            </div>
            <div class="col-6 text-center">
                __________________________________<br>
                Presidente / Direção
            </div>
        </div>
    </div>

</div>

</body>
</html>