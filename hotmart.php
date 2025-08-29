<?php
/**
 * hotmart.php
 * Biblioteca para autenticação e consumo da API da Hotmart
 */
// Inclui o autoloader do Composer.
// O caminho é '__DIR__ . '/../vendor/autoload.php'' porque 'vendor' está um nível acima
// dos arquivos PHP dentro de public_html.
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
// O caminho para o .env é '__DIR__ . '/../'' porque ele está um nível acima de public_html.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Agora você pode acessar suas credenciais usando $_ENV (ou getenv())
$clientId     = $_ENV['HOTMART_CLIENT_ID'];
$clientSecret = $_ENV['HOTMART_CLIENT_SECRET'];
$basicToken   = $_ENV['HOTMART_BASIC_TOKEN'];

// Verificação simples (opcional, mas boa prática)
if (!$clientId || !$clientSecret || !$basicToken) {
    // Isso indicaria que as variáveis de ambiente não foram carregadas.
    // Em produção, você pode logar um erro e sair.
    // Em desenvolvimento, pode deixar um valor default para testar rapidamente.
    error_log("Erro: Credenciais Hotmart não carregadas das variáveis de ambiente.");
    // Opcional: para desenvolvimento local sem .env configurado ainda
    // $clientId     = "SEU_CLIENT_ID_LOCAL_PARA_TESTE";
    // $clientSecret = "SEU_CLIENT_SECRET_LOCAL_PARA_TESTE";
    // $basicToken   = "SEU_BASIC_TOKEN_LOCAL_PARA_TESTE";
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Obtém o token de acesso da API da Hotmart
 */
function getHotmartAccessToken($clientId, $clientSecret, $basicToken) {
    $authUrl = 'https://api-sec-vlc.hotmart.com/security/oauth/token';

    $params = http_build_query([
        'grant_type' => 'client_credentials',
        'client_id' => $clientId,
        'client_secret' => $clientSecret
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $authUrl,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $basicToken
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        curl_close($ch);
        trigger_error("Erro na chamada cURL: " . $curlError, E_USER_ERROR);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    } else {
        error_log("Erro ao obter token. Código $httpCode: $response");
        return null;
    }
}

/**
 * Consulta vendas na API da Hotmart
 */
function getHotmartSales($accessToken, $startDate = null, $endDate = null) {
    $url = 'https://api-sec-vlc.hotmart.com/payments/api/v1/sales/history';

    // Adiciona filtros (datas são opcionais)
    $query = [];
    if ($startDate) $query['startDate'] = $startDate;
    if ($endDate) $query['endDate'] = $endDate;

    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $curlError = curl_error($ch);
        curl_close($ch);
        trigger_error("Erro na chamada cURL (sales): " . $curlError, E_USER_ERROR);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        return json_decode($response, true);
    } else {
        error_log("Erro ao buscar vendas. Código $httpCode: $response");
        return null;
    }
}
?>