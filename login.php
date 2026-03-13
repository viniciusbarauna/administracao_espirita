<?php
require 'config.php';
session_start();

// Se já estiver logado, redireciona pro dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    try {
        // Busca o usuário pelo email
        $stmt = $pdo->prepare("SELECT id, nome, senha_hash FROM administradores WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // Verifica se usuário existe E se a senha bate com o hash
        if ($admin && password_verify($senha, $admin['senha_hash'])) {
            // SUCESSO: Cria a sessão
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nome'] = $admin['nome'];
            
            // Dispositivo de Segurança extra: renova o ID da sessão para evitar sequestro
            session_regenerate_id(true);

            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    } catch (PDOException $e) {
        $erro = "Erro no sistema. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lar Espírita</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 15px; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .brand-icon { width: 60px; height: 60px; background: #0d6efd; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; }
    </style>
</head>
<body>

<div class="card login-card bg-white">
    <div class="card-body">
        <div class="brand-icon"><i class="fas fa-dove"></i></div>
        <h4 class="text-center mb-4 text-primary">Acesso Administrativo</h4>
        
        <?php if($erro): ?>
            <div class="alert alert-danger text-center p-2 small"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="nome@exemplo.com" required>
                <label for="email">E-mail</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                <label for="senha">Senha</label>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <small class="text-muted">Gestão Lar Espírita &copy; 2026</small>
        </div>
    </div>
</div>

</body>
</html>