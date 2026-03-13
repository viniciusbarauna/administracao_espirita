<?php
require 'auth.php';
require 'config.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $nasc = $_POST['nascimento'];
    $ingresso = $_POST['ingresso'];
    $tel = $_POST['telefone'];
    $is_zap = isset($_POST['is_whatsapp']) ? 1 : 0;
    $email = $_POST['email'];
    $periodo = $_POST['periodicidade'];
    
    // Calcular o PRIMEIRO vencimento (ex: hoje + 1 mês)
    $vencimentoInicial = calcularProximoVencimento($ingresso, $periodo);

    try {
        $sql = "INSERT INTO mensalistas (nome, data_nascimento, data_ingresso, telefone, is_whatsapp, email, periodicidade, proximo_vencimento) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $nasc, $ingresso, $tel, $is_zap, $email, $periodo, $vencimentoInicial]);

        header("Location: index.php?msg=sucesso");
        exit;

    } catch (PDOException $e) {
        die("Erro ao cadastrar: " . $e->getMessage());
    }
}
?>