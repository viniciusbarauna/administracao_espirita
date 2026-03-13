<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pagamento_id_antigo = $_POST['id_pagamento'];
    $novo_valor = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['novo_valor']);
    $nova_data = $_POST['nova_data'];
    $novo_meio = $_POST['novo_meio'];
    $motivo = $_POST['motivo'];
    $admin_id = $_SESSION['admin_id'];

    if (empty($motivo)) {
        die("Erro: É obrigatório informar o motivo da correção para auditoria.");
    }

    try {
        $pdo->beginTransaction();

        // Buscar os dados do pagamento antigo para clonar o que não mudou
        $stmt = $pdo->prepare("SELECT mensalista_id, referencia_periodo FROM pagamentos WHERE id = ?");
        $stmt->execute([$pagamento_id_antigo]);
        $antigo = $stmt->fetch();

        if (!$antigo) die("Pagamento original não encontrado.");

        // Inserir o NOVO pagamento corrigido
        $sqlNovo = "INSERT INTO pagamentos (mensalista_id, admin_id, valor, meio_pagamento, data_pagamento, referencia_periodo, motivo_correcao) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtNovo = $pdo->prepare($sqlNovo);
        $stmtNovo->execute([
            $antigo['mensalista_id'], 
            $admin_id, // Quem fez a correção assina o novo registro
            $novo_valor, 
            $novo_meio, 
            $nova_data, 
            $antigo['referencia_periodo'], 
            "Correção: " . $motivo
        ]);
        
        $id_novo_pagamento = $pdo->lastInsertId();

        // "Invalidar" o pagamento antigo apontando para o novo
        // Efeito de "riscado" para auditoria
        $sqlUpdate = $pdo->prepare("UPDATE pagamentos SET substituido_por_id = ? WHERE id = ?");
        $sqlUpdate->execute([$id_novo_pagamento, $pagamento_id_antigo]);

        $pdo->commit();
        header("Location: index.php?msg=correcao_ok");

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Erro na auditoria: " . $e->getMessage());
    }
}
?>