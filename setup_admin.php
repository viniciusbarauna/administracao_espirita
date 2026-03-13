<?php
require 'config.php';

// Dados do temporário Admin para teste
$nome = "Admin Mestre";
$email = "admin@lar.com";
$senha = "admin123";

// Hash seguro
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("REPLACE INTO administradores (id, nome, email, senha_hash) VALUES (1, ?, ?, ?)");
    $stmt->execute([$nome, $email, $senhaHash]);
    
    echo "<h1>Sucesso!</h1>";
    echo "<p>Usuário: <strong>$email</strong></p>";
    echo "<p>Senha: <strong>$senha</strong></p>";
    echo "<a href='login.php'>Ir para Login</a>";
    
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>