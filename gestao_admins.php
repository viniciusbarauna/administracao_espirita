<?php
require 'auth.php';
require 'config.php';

// Busca todos os admins
$stmt = $pdo->query("SELECT id, nome, email, criado_em FROM administradores ORDER BY nome ASC");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Administradores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left me-2"></i>Voltar ao Dashboard</a>
        <span class="navbar-text text-white">Gestão de Acessos</span>
    </div>
</nav>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Administradores do Sistema</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNovoAdmin">
            <i class="fas fa-user-plus me-2"></i>Novo Admin
        </button>
    </div>

    <?php if(isset($_GET['erro']) && $_GET['erro'] == 'mestre'): ?>
        <div class="alert alert-danger">Você não pode excluir o Administrador Mestre!</div>
    <?php endif; ?>
    
    <?php if(isset($_GET['erro']) && $_GET['erro'] == 'proprio'): ?>
        <div class="alert alert-danger">Você não pode excluir seu próprio usuário enquanto está logado.</div>
    <?php endif; ?>

    <?php if(isset($_GET['erro']) && $_GET['erro'] == 'proibido_mestre'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-ban me-2"></i>Ação Negada: Apenas o Administrador Mestre pode alterar login/senha da conta Mestre.
        </div>
    <?php endif; ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email (Login)</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($admins as $a): ?>
                    <tr>
                        <td>#<?= $a['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($a['nome']) ?></strong>
                            <?php if($a['id'] == 1): ?> 
                                <span class="badge bg-warning text-dark ms-1">Mestre</span> 
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($a['email']) ?></td>
                        <td>
                            <?php 
                                // LÓGICA DE PROTEÇÃO DO BOTÃO EDITAR
                                // Se o alvo for o Mestre (ID 1) E eu NÃO sou o Mestre, bloqueia.
                                $podeEditar = true;
                                if ($a['id'] == 1 && $_SESSION['admin_id'] != 1) {
                                    $podeEditar = false;
                                }
                            ?>

                            <?php if ($podeEditar): ?>
                                <button class="btn btn-sm btn-outline-primary" 
                                    onclick="abrirModalEditar(<?= $a['id'] ?>, '<?= $a['nome'] ?>', '<?= $a['email'] ?>')">
                                    <i class="fas fa-key me-1"></i> Editar / Senha
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-light text-muted" disabled title="Apenas o Mestre altera seus dados">
                                    <i class="fas fa-lock me-1"></i> Protegido
                                </button>
                            <?php endif; ?>
                            
                            <?php if($a['id'] != 1 && $a['id'] != $_SESSION['admin_id']): ?>
                                <a href="admin_actions.php?acao=excluir&id=<?= $a['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Tem certeza que deseja remover este administrador?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-light text-muted" disabled><i class="fas fa-trash"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNovoAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin_actions.php" method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Administrador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nome Completo</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>E-mail de Login</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Senha Inicial</label>
                        <input type="password" name="senha" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Cadastrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarAdmin" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin_actions.php" method="POST">
                <input type="hidden" name="acao" value="editar">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Editar Dados & Senha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> Deixe o campo "Nova Senha" em branco para manter a senha atual.
                    </div>
                    <div class="mb-3">
                        <label>Nome</label>
                        <input type="text" name="nome" id="edit_nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>E-mail (Login)</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="fw-bold text-primary">Nova Senha (Opcional)</label>
                        <input type="password" name="senha" class="form-control" placeholder="Digite apenas para mudar a senha...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function abrirModalEditar(id, nome, email) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditarAdmin'));
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nome').value = nome;
        document.getElementById('edit_email').value = email;
        modal.show();
    }
</script>
</body>
</html>