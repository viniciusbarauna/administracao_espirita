<?php
require 'auth.php';
require 'config.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensalista_id = $_POST['mensalista_id'];
    
    // Tratamento de valor
    $valor = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor']);
    $meio = $_POST['meio_pagamento'];
    
    $data_pag = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : date('Y-m-d');
    
    $admin_responsavel = $_SESSION['admin_id']; 

    if(empty($mensalista_id) || empty($valor)) {
        die("Erro: Dados incompletos.");
    }

    try {
        $pdo->beginTransaction();

        // Busca os dados
        $stmt = $pdo->prepare("SELECT periodicidade, proximo_vencimento FROM mensalistas WHERE id = ?");
        $stmt->execute([$mensalista_id]);
        $m = $stmt->fetch();

        // Calcula novo vencimento
        $novoVencimento = calcularProximoVencimento($m['proximo_vencimento'], $m['periodicidade']);

        // Registra Pagamento
        $sqlPag = "INSERT INTO pagamentos (mensalista_id, admin_id, valor, meio_pagamento, data_pagamento, referencia_periodo) 
                   VALUES (?, ?, ?, ?, ?, ?)";
        $stmtPag = $pdo->prepare($sqlPag);
        
        // Referência Visual
        $ref = $m['periodicidade'] . " - Ref: " . date('m/Y'); 
        
        // Passa $data_pag corrigida
        $stmtPag->execute([$mensalista_id, $admin_responsavel, $valor, $meio, $data_pag, $ref]);

        // Atualiza Mensalista
        $stmtUp = $pdo->prepare("UPDATE mensalistas SET proximo_vencimento = ? WHERE id = ?");
        $stmtUp->execute([$novoVencimento, $mensalista_id]);

        $pdo->commit();
        header("Location: index.php?msg=pagamento_ok");

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao processar: " . $e->getMessage());
    }
}
?>