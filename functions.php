<?php

// Sanitização para evitar XSS (Cross-Site Scripting) na exibição
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// LGPD: Mascarar dados sensíveis em relatórios gerais
function mascararEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $email;
    $partes = explode("@", $email);
    $nome = substr($partes[0], 0, 2) . "****";
    return $nome . "@" . $partes[1];
}

// Motor de Template de Mensagem
function processarMensagem($template, $dadosMensalista, $diasParaVencer) {

    $placeholders = [
        '{{nome}}' => explode(' ', $dadosMensalista['nome'])[0], // Pega só o primeiro nome
        '{{prazo}}' => $diasParaVencer . " dias",
        '{{data_vencimento}}' => date('d/m/Y', strtotime($dadosMensalista['proximo_vencimento']))
    ];

    return str_replace(array_keys($placeholders), array_values($placeholders), $template);
}

// Calcular próxima data baseado na periodicidade
function calcularProximoVencimento($dataAtual, $periodicidade) {
    $data = new DateTime($dataAtual);
    switch ($periodicidade) {
        case 'Mensal': $data->modify('+1 month'); break;
        case 'Bimestral': $data->modify('+2 months'); break;
        case 'Trimestral': $data->modify('+3 months'); break;
        case 'Semestral': $data->modify('+6 months'); break;
        case 'Anual': $data->modify('+1 year'); break;
    }
    return $data->format('Y-m-d');
}
?>