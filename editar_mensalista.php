<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $tel = $_POST['telefone'];
    $email = $_POST['email'];
    $periodo = $_POST['periodicidade'];
    $is_zap = isset($_POST['is_whatsapp']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("UPDATE mensalistas SET nome=?, telefone=?, email=?, periodicidade=?, is_whatsapp=? WHERE id=?");
        $stmt->execute([$nome, $tel, $email, $periodo, $is_zap, $id]);
        
        header("Location: index.php?msg=editado");
    } catch (PDOException $e) {
        die("Erro ao editar: " . $e->getMessage());
    }
}
?>