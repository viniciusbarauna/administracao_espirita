<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texto = $_POST['texto_padrao'];
    $stmt = $pdo->prepare("INSERT INTO templates_mensagem (id, tipo, texto) VALUES (1, 'padrao', ?) ON DUPLICATE KEY UPDATE texto = ?");
    $stmt->execute([$texto, $texto]);
    $sucesso = "Template atualizado!";
}

$stmt = $pdo->query("SELECT texto FROM templates_mensagem WHERE id = 1");
$atual = $stmt->fetchColumn();
if(!$atual) $atual = "Olá {{nome}}, sua mensalidade vence em {{prazo}}.";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Mensagem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="card max-w-lg mx-auto" style="max-width: 800px;">
        <div class="card-header bg-primary text-white">Configurar Modelo de Mensagem</div>
        <div class="card-body">
            <?php if(isset($sucesso)) echo "<div class='alert alert-success'>$sucesso</div>"; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Texto Padrão para Cobrança</label>
                    <textarea name="texto_padrao" class="form-control" rows="8"><?= htmlspecialchars($atual) ?></textarea>
                    <div class="form-text">
                        Use <strong>{{nome}}</strong> para o nome do mensalista e <strong>{{prazo}}</strong> para o tempo restante.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Salvar Modelo</button>
                <a href="index.php" class="btn btn-secondary">Voltar</a>
            </form>
        </div>
    </div>
</body>
</html>