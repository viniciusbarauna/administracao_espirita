<?php
require 'auth.php';
require 'config.php';
require 'functions.php';

// Consultas para os Cards de Estatísticas
try {
    // Total de Ativos
    $stmtTotal = $pdo->query("SELECT COUNT(*) FROM mensalistas WHERE status = 'Ativo'");
    $totalAtivos = $stmtTotal->fetchColumn();

    // Vencendo nos próximos 7 dias
    $stmtAlerta = $pdo->query("SELECT COUNT(*) FROM mensalistas WHERE status = 'Ativo' AND proximo_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
    $totalAlerta = $stmtAlerta->fetchColumn();

    // Vencidos (Data menor que hoje)
    $stmtVencidos = $pdo->query("SELECT COUNT(*) FROM mensalistas WHERE status = 'Ativo' AND proximo_vencimento < CURDATE()");
    $totalVencidos = $stmtVencidos->fetchColumn();

    // Buscar a lista de mensalistas ordenados por urgência (Vencidos primeiro)
    $sqlLista = "SELECT * FROM mensalistas WHERE status = 'Ativo' ORDER BY proximo_vencimento ASC";
    $stmtLista = $pdo->query($sqlLista);
    $mensalistas = $stmtLista->fetchAll();

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

$stmtTemp = $pdo->query("SELECT texto FROM templates_mensagem WHERE id = 1");
$templateDb = $stmtTemp->fetchColumn();

if(!$templateDb) $templateDb = "Olá {{nome}}, vencimento em {{prazo}}.";
// Escapar quebras de linha para não quebrar o JS
$templateJs = str_replace(["\r", "\n"], ['\r', '\n'], addslashes($templateDb));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Lar Espírita - Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        .card-stat { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table-container { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .status-badge { font-size: 0.85em; padding: 5px 10px; border-radius: 20px; }
        .status-ok { background-color: #d1e7dd; color: #0f5132; }
        .status-warn { background-color: #fff3cd; color: #664d03; }
        .status-danger { background-color: #f8d7da; color: #842029; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">    
    <a class="navbar-brand" href="#"><i class="fas fa-dove me-2"></i>Lar Espírita - Gestão</a>
    <a href="gestao_admins.php" class="btn btn-outline-light ms-2">
    <i class="fas fa-user-shield me-2"></i>Gerir Admins</a>
    <a href="config_mensagem.php" class="btn btn-outline-light">⚙️ Configurar Texto</a>
    
    <a href="logout.php" class="btn btn-sm btn-outline-light">Sair</a>
</div>
</nav>

<div class="container">
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-stat bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted">Total de Mensalistas</h6><h3><?= $totalAtivos ?></h3></div>
                    <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted">Vencendo (7 dias)</h6><h3 class="text-warning"><?= $totalAlerta ?></h3></div>
                    <i class="fas fa-clock fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stat bg-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><h6 class="text-muted">Vencidos</h6><h3 class="text-danger"><?= $totalVencidos ?></h3></div>
                    <i class="fas fa-exclamation-circle fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mb-3">
    <div> <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoMensalista">
            <i class="fas fa-plus me-2"></i>Novo Mensalista
        </button>
        <a href="relatorio_financeiro.php" class="btn btn-outline-dark ms-2">
            <i class="fas fa-chart-line me-2"></i> Relatórios
        </a>
    </div>
</div>

    <div class="table-container">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nome / Plano</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th>Contato</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($mensalistas) > 0): ?>
                    <?php foreach($mensalistas as $m): 

                        $hoje = new DateTime();
                        $venc = new DateTime($m['proximo_vencimento']);
                        $diff = $hoje->diff($venc);
                        $dias = (int)$diff->format("%r%a");

                        if ($dias < 0) {
                            $classe = 'status-danger';
                            $textoStatus = 'Vencido há ' . abs($dias) . ' dias';
                            $prazoMsg = "vencida há " . abs($dias) . " dias";
                        } elseif ($dias <= 7) {
                            $classe = 'status-warn';
                            $textoStatus = 'Vence em ' . $dias . ' dias';
                            $prazoMsg = "daqui a " . $dias . " dias";
                        } else {
                            $classe = 'status-ok';
                            $textoStatus = 'Em dia';
                            $prazoMsg = "em breve";
                        }
                    ?>
                    <tr>
                        <td>
                            <strong><?= e($m['nome']) ?></strong><br>
                            <small class="text-muted"><?= e($m['periodicidade']) ?></small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($m['proximo_vencimento'])) ?></td>
                        <td><span class="status-badge <?= $classe ?>"><?= $textoStatus ?></span></td>
                        <td>
                            <?php if($m['is_whatsapp']): ?>
                                <i class="fab fa-whatsapp text-success me-1"></i>
                            <?php else: ?>
                                <i class="fas fa-phone text-secondary me-1"></i>
                            <?php endif; ?>
                            <?= e($m['telefone']) ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" 
                                onclick="abrirModalMensagem(<?= $m['id'] ?>, '<?= e($m['nome']) ?>', '<?= $prazoMsg ?>')">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success" 
                                onclick="abrirModalPagamento(<?= $m['id'] ?>, '<?= e($m['nome']) ?>')">
                                <i class="fas fa-hand-holding-usd"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-warning" 
                                onclick='abrirModalEditar(<?= htmlspecialchars(json_encode($m), ENT_QUOTES, 'UTF-8') ?>)'>
                                 <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info" 
                                onclick="abrirModalHistorico(<?= $m['id'] ?>, '<?= $m['nome'] ?>')" 
                                title="Ver Histórico de Pagamentos">
                                <i class="fas fa-history"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">Nenhum mensalista cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalNovoMensalista" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <a href="relatorio_financeiro.php" class="btn btn-outline-dark ms-2">
                        <i class="fas fa-chart-line me-2"></i> Relatórios de Caixa
                        </a>
                <h5 class="modal-title">Novo Mensalista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <form action="cadastro_mensalista.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6"><label>Nome</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-3"><label>Nascimento</label><input type="date" name="nascimento" class="form-control" required></div>
                        <div class="col-md-3"><label>Ingresso</label><input type="date" name="ingresso" class="form-control" required></div>
                        <div class="col-md-6"><label>Telefone</label><input type="text" name="telefone" class="form-control" required></div>
                        <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-md-6">
                            <label>Periodicidade</label>
                            <select name="periodicidade" class="form-select">
                                <option value="Mensal">Mensal</option>
                                <option value="Bimestral">Bimestral</option>
                                <option value="Trimestral">Trimestral</option>
                                <option value="Semestral">Semestral</option>
                                <option value="Anual">Anual</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="is_whatsapp" class="form-check-input" checked>
                                <label class="form-check-label">É WhatsApp</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3"><button type="submit" class="btn btn-primary">Salvar</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMensagem" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="processar_envio.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fab fa-whatsapp me-2"></i>Enviar Notificação</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="msg_id_mensalista" name="ids[]"> 
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Enviar por:</label>
                        <div class="btn-group w-100" role="group">
                            <input type="checkbox" class="btn-check" id="chk_zap" name="canais[]" value="whatsapp" checked>
                            <label class="btn btn-outline-success" for="chk_zap">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </label>
                            <input type="checkbox" class="btn-check" id="chk_sms" name="canais[]" value="sms">
                            <label class="btn btn-outline-primary" for="chk_sms">
                                <i class="fas fa-comment"></i> SMS
                            </label>
                            <input type="checkbox" class="btn-check" id="chk_email" name="canais[]" value="email">
                            <label class="btn btn-outline-secondary" for="chk_email">
                                <i class="far fa-envelope"></i> Email
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensagem Personalizada:</label>
                        <textarea class="form-control" id="msg_texto" name="mensagem" rows="6"></textarea>
                        <small class="text-muted">Variáveis: {{nome}}, {{prazo}}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar Agora</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPagamento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="renovar.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Renovar: <span id="pag_nome_mensalista"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="mensalista_id" id="pag_id_mensalista">
                    <div class="mb-3"><label>Valor</label><input type="number" step="0.01" name="valor" class="form-control" required></div>
                    <div class="mb-3"><label>Meio</label>
                        <select name="meio_pagamento" class="form-select">
                            <option>PIX</option><option>Dinheiro</option><option>Cartao</option>
                        </select>
                        <label class="form-label">Data Pagamento</label>
                        <input type="date" class="form-control" name="data_pagamento" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Confirmar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorico" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Auditoria Financeira
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="mb-3">Pagamentos de: <strong id="hist_nome_mensalista"></strong></h6>
                
                <div id="conteudo_historico" class="text-center p-3">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p>Carregando dados...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCorrecao" tabindex="-1" style="z-index: 1060;"> <div class="modal-dialog">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Corrigir Lançamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="alert alert-warning small">
                    <i class="fas fa-info-circle"></i> <strong>Auditoria:</strong> O valor antigo não será apagado. Ele ficará riscado no histórico para segurança.
                </div>
                
                <form action="corrigir_pagamento.php" method="POST">
                    <input type="hidden" name="id_pagamento" id="corr_id">
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <label>Valor Correto</label>
                            <input type="text" name="novo_valor" id="corr_valor" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label>Data Correta</label>
                            <input type="date" name="nova_data" id="corr_data" class="form-control" required>
                        </div>
                        <div class="col-12 mt-2">
                            <label>Meio de Pagamento</label>
                            <select name="novo_meio" id="corr_meio" class="form-select">
                                <option value="PIX">PIX</option>
                                <option value="Dinheiro">Dinheiro</option>
                                <option value="Cartao">Cartão</option>
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <label class="fw-bold">Motivo da Correção (Obrigatório)</label>
                            <textarea name="motivo" class="form-control" rows="2" placeholder="Ex: Digitei o valor errado..." required></textarea>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-end">
                        <button type="submit" class="btn btn-dark">Confirmar Correção</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarMensalista" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Editar Cadastro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="editar_mensalista.php" method="POST">
                    <input type="hidden" name="id" id="edit_m_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Nome Completo</label>
                            <input type="text" name="nome" id="edit_m_nome" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="edit_m_telefone" class="form-control" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_whatsapp" id="edit_m_zap">
                                <label class="form-check-label">É WhatsApp</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_m_email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Periodicidade</label>
                            <select name="periodicidade" id="edit_m_periodo" class="form-select">
                                <option value="Mensal">Mensal</option>
                                <option value="Bimestral">Bimestral</option>
                                <option value="Trimestral">Trimestral</option>
                                <option value="Semestral">Semestral</option>
                                <option value="Anual">Anual</option>
                            </select>
                        </div>
                         </div>
                    <div class="modal-footer mt-3">
                        <button type="submit" class="btn btn-warning">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scripts JS para receber ID
    function abrirModalMensagem(id, nome, prazo) {
    const modal = new bootstrap.Modal(document.getElementById('modalMensagem'));
    document.getElementById('msg_id_mensalista').value = id;
    
    // Usei a variável PHP dentro do JS
    let template = "<?= $templateJs ?>"; // Pega do banco
    
    // Substitui
    template = template.replace('{{nome}}', nome);
    template = template.replace('{{prazo}}', prazo);
    
    document.getElementById('msg_texto').value = template;
    modal.show();
}

    function abrirModalPagamento(id, nome) {
        const modal = new bootstrap.Modal(document.getElementById('modalPagamento'));
        document.getElementById('pag_id_mensalista').value = id;
        document.getElementById('pag_nome_mensalista').innerText = nome;
        modal.show();
    }
    function abrirModalEditar(dados) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditarMensalista'));
        
        // Preencher os campos
        document.getElementById('edit_m_id').value = dados.id;
        document.getElementById('edit_m_nome').value = dados.nome;
        document.getElementById('edit_m_telefone').value = dados.telefone;
        document.getElementById('edit_m_email').value = dados.email;
        document.getElementById('edit_m_periodo').value = dados.periodicidade;
        
        document.getElementById('edit_m_zap').checked = (dados.is_whatsapp == 1);
        
        modal.show();
    }
    function abrirModalHistorico(id, nome) {
        const modal = new bootstrap.Modal(document.getElementById('modalHistorico'));
        document.getElementById('hist_nome_mensalista').innerText = nome;
        
        const divConteudo = document.getElementById('conteudo_historico');
        divConteudo.innerHTML = '<div class="spinner-border text-primary"></div><p>Buscando registros...</p>';
        
        modal.show();

        // Fetch via AJAX
        fetch(`get_historico.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                divConteudo.innerHTML = html;
            })
            .catch(err => {
                divConteudo.innerHTML = '<div class="alert alert-danger">Erro ao carregar histórico.</div>';
            });
    }
    // Função para abrir o modal de correção (Chamada de dentro do get_historico.php)
    function abrirModalCorrecao(id, valor, data, meio) {

        const modal = new bootstrap.Modal(document.getElementById('modalCorrecao'));
        
        document.getElementById('corr_id').value = id;
        document.getElementById('corr_valor').value = valor;
        document.getElementById('corr_data').value = data; // Formato YYYY-MM-DD
        document.getElementById('corr_meio').value = meio;
        
        modal.show();
    }
</script>
</body>
</html>