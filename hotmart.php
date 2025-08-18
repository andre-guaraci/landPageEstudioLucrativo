<?php
/**
 * Arquivo: hotmart.php
 * Descrição: Exemplo de como fazer uma chamada à API da Hotmart para obter o token de acesso.
 * IMPORTANTE: Este é um exemplo de back-end. Para funcionar, você precisa de um ambiente com PHP (como a Hostinger).
 * Você NUNCA deve expor seu Client ID e Client Secret no front-end (HTML/JS).
 */

// Esconde erros para não quebrar a aplicação em produção, mas loga-os.
ini_set('display_errors', 0);
ini_set('log_errors', 1);


 // Função para obter o token de acesso da API da Hotmart.
/*
    @param string $clientId O seu Client ID da Hotmart.
    @param string $clientSecret O seu Client Secret da Hotmart.
    @param string $basicToken O seu Basic Token (geralmente base64_encode(clientId:clientSecret)).
    @return string|null O token de acesso ou null em caso de falha.
*/
 
function getHotmartAccessToken($clientId, $clientSecret, $basicToken) {
    // URL de autenticação da Hotmart. Mude para o endpoint de produção se necessário.
    $authUrl = '[https://api-sec-vlc.hotmart.com/security/oauth/token](https://api-sec-vlc.hotmart.com/security/oauth/token)';

    // Parâmetros para a requisição do token.
    $params = http_build_query([
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret
    ]);

    // Configuração do cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $authUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ' . $basicToken
    ]);

    // Executa a requisição
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Verifica se a requisição foi bem-sucedida
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    } else {
        // Em um caso real, você logaria o erro.
        // error_log("Erro ao obter token da Hotmart: " . $response);
        return null;
    }
}


// --- COMO USAR ---
// 1. Obtenha suas credenciais no painel da Hotmart.
// 2. Substitua os valores abaixo.

// $clientId = 'SEU_CLIENT_ID';
// $clientSecret = 'SEU_CLIENT_SECRET';
// $basicToken = base64_encode($clientId . ':' . $clientSecret);

// $accessToken = getHotmartAccessToken($clientId, $clientSecret, $basicToken);

// if ($accessToken) {
//     // Você pode usar este token para fazer outras chamadas à API,
//     // como buscar informações do produto, histórico de vendas, etc.
//     // Exemplo: header('Content-Type: application/json'); echo json_encode(['token' => $accessToken]);
// } else {
//     // header('Content-Type: application/json');
//     // http_response_code(500);
//     // echo json_encode(['error' => 'Não foi possível obter o token de acesso.']);
// }

/**
 * Para a landing page, a forma mais direta de integração é usar o Link de Pagamento (HotLink)
 * diretamente no botão de CTA, como já está no arquivo HTML.
 *
 * <a href="[SEU_LINK_DE_CHECKOUT_DA_HOTMART]" class="cta-button">...</a>
 *
 * Este script PHP serve como uma base para integrações mais avançadas que você
 * queira fazer no futuro (ex: criar uma área de membros customizada, etc.).
 */
?>