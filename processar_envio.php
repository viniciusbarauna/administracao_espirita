<?php
// EXIBIÇÃO DE ERROS (Para Debugar o código)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'auth.php';
require 'config.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Erro: Acesso inválido. <a href='index.php'>Voltar</a>");
}

// RECEBER E LIMPAR DADOS
$ids = $_POST['ids'] ?? null;
$canais = $_POST['canais'] ?? [];
$mensagemBase = $_POST['mensagem'] ?? '';

$id_final = is_array($ids) ? ($ids[0] ?? null) : $ids;

if (!$id_final) {
    die("Erro: Nenhum mensalista selecionado. Volte e tente novamente.");
}

if (empty($canais)) {
    die("Erro: Nenhum canal de envio (WhatsApp/Email) foi selecionado.");
}

try {
    // BUSCAR DADOS DO MENSALISTA
    $stmt = $pdo->prepare("SELECT nome, telefone, email, proximo_vencimento FROM mensalistas WHERE id = ?");
    $stmt->execute([$id_final]);
    $m = $stmt->fetch();

    if (!$m) {
        die("Erro: Mensalista ID #$id_final não encontrado no banco de dados.");
    }

    // PREPARAR A MENSAGEM
    $hoje = new DateTime();
    $venc = new DateTime($m['proximo_vencimento']);
    $diff = $hoje->diff($venc);
    
    if ($diff->invert) {
        $diasTexto = "venceu há " . $diff->days . " dias";
    } elseif ($diff->days == 0) {
        $diasTexto = "vence hoje";
    } else {
        $diasTexto = "vence em " . $diff->days . " dias";
    }

    $msgFinal = processarMensagem($mensagemBase, $m, $diasTexto);

    // ENVIAR (ROTEAMENTO)
    
    // --> WHATSAPP
    if (in_array('whatsapp', $canais)) {
        $fone = preg_replace('/[^0-9]/', '', $m['telefone']);
        
        if (empty($fone)) {
            die("Erro: Este mensalista não tem telefone cadastrado.");
        }

        // Adiciona 55 se tiver 10 ou 11 dígitos (DDD + Número)
        if (strlen($fone) >= 10 && strlen($fone) <= 11) { 
            $fone = "55" . $fone; 
        }

        $textoUrl = urlencode($msgFinal);
        
        // Link Universal do WhatsApp
        $linkZap = "https://wa.me/{$fone}?text={$textoUrl}";
        
        // Redirect
        header("Location: $linkZap");
        exit;
    }

    // --> SMS
    if (in_array('sms', $canais)) {
        $fone = preg_replace('/[^0-9]/', '', $m['telefone']);
        if (strlen($fone) <= 11) $fone = "55" . $fone; // Padrão internacional

        // ============================================================
        // CONFIGURAÇÃO DA API DE SMS (Exemplo Genérico)
        // Aqui colocarei o serviço de API que será ou não contratado pelo lar espírita
        // ============================================================
        $apiUrl = 'https://api.exemplo-sms.com.br/send'; // URL da API contratada
        $apiKey = 'CHAVE_API'; 
        
        // Dados a enviar (Pode mudar conforme a documentação da empresa contratada)
        $data = [
            'key' => $apiKey,
            'number' => $fone,
            'msg' => $msgFinal,
            'type' => 'text'
        ];

        // Disparo via cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Descomentarei abaixo para enviar de verdade quando tivermos a API contratada e configurada
        // $response = curl_exec($ch);
        // curl_close($ch);

        // MODO SIMULAÇÃO (Para testar como ficará no futuro, mas que não envia nada)
        echo "<h3>Simulação de SMS</h3>";
        echo "<p><strong>Para:</strong> $fone</p>";
        echo "<p><strong>Texto:</strong> $msgFinal</p>";
        echo "<div class='alert alert-warning'>Nota: Para enviar SMS real, configure a URL e API Key no arquivo 'processar_envio.php'.</div>";
        echo "<a href='index.php'>Voltar</a>";
        exit;
    }

    // --> EMAIL (Simulação)
    if (in_array('email', $canais)) {
        echo "<h3>Simulação de Envio de E-mail</h3>";
        echo "<p><strong>Para:</strong> " . htmlspecialchars($m['email']) . "</p>";
        echo "<p><strong>Mensagem:</strong><br>" . nl2br(htmlspecialchars($msgFinal)) . "</p>";
        echo "<br><a href='index.php' style='padding: 10px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Voltar ao Dashboard</a>";
        exit;
    }

} catch (PDOException $e) {
    die("Erro Crítico no Banco de Dados: " . $e->getMessage());
}
?>