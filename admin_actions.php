<?php
require 'auth.php';
require 'config.php';
require 'functions.php';

// Verifica qual ação está sendo solicitada
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

try {
    // CRIAR NOVO ADMINISTRADOR
    if ($acao === 'criar') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        // Verifica se email já existe
        $stmtCheck = $pdo->prepare("SELECT id FROM administradores WHERE email = ?");
        $stmtCheck->execute([$email]);
        if ($stmtCheck->rowCount() > 0) {
            die("Erro: Esse e-mail já está cadastrado.");
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO administradores (nome, email, senha_hash) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $senhaHash]);

        header("Location: gestao_admins.php?msg=criado");
        exit;
    }

    // EDITAR / RESETAR SENHA
    if ($acao === 'editar') {
        $id = $_POST['id'];
        
        if ($id == 1 && $_SESSION['admin_id'] != 1) {
            header("Location: gestao_admins.php?erro=proibido_mestre");
            exit;
        }

        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $novaSenha = $_POST['senha']; 

        $sql = "UPDATE administradores SET nome = ?, email = ?";
        $params = [$nome, $email];

        // Se o admin digitou algo no campo senha, atualiza ela
        if (!empty($novaSenha)) {
            $sql .= ", senha_hash = ?";
            $params[] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        header("Location: gestao_admins.php?msg=atualizado");
        exit;
    }

    // EXCLUIR ADMINISTRADOR
    if ($acao === 'excluir') {
        $id = $_GET['id'];

        // DISPOSITIVO DE SEGURANÇA: Não deixar excluir o ID 1 (Mestre) nem a si mesmo
        if ($id == 1) {
            header("Location: gestao_admins.php?erro=mestre");
            exit;
        }
        if ($id == $_SESSION['admin_id']) {
            header("Location: gestao_admins.php?erro=proprio");
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM administradores WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: gestao_admins.php?msg=excluido");
        exit;
    }

} catch (PDOException $e) {
    die("Erro no banco de dados: " . $e->getMessage());
}
?>