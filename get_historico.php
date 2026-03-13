<?php
require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? 0;

try {
    // Busca tudo
    $sql = "SELECT p.*, a.nome as admin_nome 
            FROM pagamentos p 
            JOIN administradores a ON p.admin_id = a.id 
            WHERE p.mensalista_id = ? 
            ORDER BY p.data_pagamento DESC, p.id DESC"; 
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $pagamentos = $stmt->fetchAll();

    if (count($pagamentos) === 0) {
        echo "<div class='alert alert-info'>Nenhum registro financeiro.</div>";
    } else {
        echo "<table class='table table-sm align-middle'>";
        echo "<thead class='table-dark'><tr>
                <th>Data</th>
                <th>Valor</th>
                <th>Meio</th>
                <th>Admin / Audit</th>
                <th>Ação</th>
              </tr></thead><tbody>";

        foreach ($pagamentos as $p) {
            $dataF = date('d/m/Y', strtotime($p['data_pagamento']));
            $valorF = number_format($p['valor'], 2, ',', '.');
            
            // Usei '?? null' para garantir que não dê erro se a coluna não existir
            $substituido = $p['substituido_por_id'] ?? null;
            $motivo = $p['motivo_correcao'] ?? '';

            // Se tiver sido substituído, mostra riscado
            if ($substituido != null) {
                echo "<tr class='text-muted' style='background-color: #f8f9fa;'>
                        <td style='text-decoration: line-through;'>{$dataF}</td>
                        <td style='text-decoration: line-through;'>R$ {$valorF}</td>
                        <td style='text-decoration: line-through;'>{$p['meio_pagamento']}</td>
                        <td>
                            <small class='d-block text-decoration-line-through'>{$p['admin_nome']}</small>
                            <span class='badge bg-danger' style='font-size: 0.6em'>CORRIGIDO</span>
                        </td>
                        <td><i class='fas fa-lock text-muted' title='Registro Auditado'></i></td>
                      </tr>";
            } else {
                // Registro Válido
                echo "<tr>
                        <td class='fw-bold'>{$dataF}</td>
                        <td class='text-success fw-bold'>R$ {$valorF}</td>
                        <td>{$p['meio_pagamento']}</td>
                        <td>
                            {$p['admin_nome']}
                            " . ($motivo ? "<br><small class='text-info fst-italic'>{$motivo}</small>" : "") . "
                        </td>
                        <td>
                            <button class='btn btn-xs btn-outline-warning' 
                                onclick='abrirModalCorrecao({$p['id']}, \"{$p['valor']}\", \"{$p['data_pagamento']}\", \"{$p['meio_pagamento']}\")'
                                title='Corrigir Erro'>
                                <i class='fas fa-eraser'></i>
                            </button>
                        </td>
                      </tr>";
            }
        }
        echo "</tbody></table>";
    }

} catch (PDOException $e) {
    echo "Erro ao buscar histórico: " . $e->getMessage();
}
?>