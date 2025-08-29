<?php
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

require_once "hotmart.php";

header('Content-Type: application/json');

$accessToken = getHotmartAccessToken($clientId, $clientSecret, $basicToken);

if (!$accessToken) {
    echo json_encode(["error" => "Falha ao obter token de acesso Hotmart."]);
    exit;
}

$url = "https://developers.hotmart.com/products/api/v1/products"; // Endpoint correto e funcionando

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $accessToken",
        "Content-Type: application/json"
    ],
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $curlError = curl_error($ch);
    curl_close($ch);
    echo json_encode(["error" => "Erro na requisição cURL: " . $curlError]);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("Erro ao buscar produtos. Código $httpCode: $response");
    echo json_encode(["error" => "Erro da API Hotmart (HTTP $httpCode)", "response" => $response]);
    exit;
}

// =========================================================================
// !!! SEÇÃO DE MAPEAMENTO PERSONALIZADO DE IMAGENS E LINKS DE CHECKOUT !!!
// =========================================================================
$productCustomData = [
    // Use o 'id' de cada produto Hotmart como chave e forneça sua imagem e link personalizado
    // Você pode ver os IDs no JSON que a Hotmart retorna (ex: 6115722, 5971653, etc.)
    6115722 => [ // Exemplo: Checklist Profissional para o Tatuador
        'imagem' => '/img/Capa_Checklist.png', // URL da sua imagem
        'link'   => 'https://pay.hotmart.com/SEUID12345C', // Seu link de checkout específico ou página de vendas
        'descricao_custom' => 'Um guia completo para otimizar seu estúdio e processos.' // Descrição personalizada (opcional)
    ],
    5971653 => [ // Exemplo: Conteúdo Premium para Tatuadores Atraindo Clientes...
        'imagem' => '/img/Capa_Atraindo_Clientes_Pagam.png',
        'link'   => 'https://pay.hotmart.com/SEUID67890C',
        'descricao_custom' => 'Estratégias avançadas para atrair clientes que valorizam seu trabalho.'
    ],
    6031246 => [ // Exemplo: Kit de Scripts para WhatsApp
        'imagem' => '/img/Capa_Kit_Scripts.png',
        'link'   => 'https://pay.hotmart.com/SEUID11223C',
        'descricao_custom' => 'Scripts prontos para engajar e converter clientes no WhatsApp.'
    ],
    5965235 => [ // Exemplo: Metodo de Agenda Cheia para Tatuadores + Bonus
        'imagem' => '/img/Capa_Metodo_Agenda_Cheia.png',
        'link'   => 'https://pay.hotmart.com/I101068055X?checkoutMode=10',
        'descricao_custom' => 'Descubra o método para ter sua agenda sempre lotada de tatuagens!'
    ],
    6116134 => [ // Exemplo: Roteiro de atendimento encantador para Tatuadores
        'imagem' => '/img/Capa_Roteiro_Atendimento.png',
        'link'   => 'https://pay.hotmart.com/SEUID77889C',
        'descricao_custom' => 'Domine a arte de um atendimento que encanta e fideliza clientes.'
    ],
    // Adicione mais produtos aqui, seguindo o padrão
];
// =========================================================================

$hotmartData = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($hotmartData['items']) || !is_array($hotmartData['items'])) {
    error_log("Estrutura de resposta da Hotmart inesperada ou JSON inválido: " . $response);
    echo json_encode(["error" => "Erro ao processar dados da Hotmart. Formato inesperado."]);
    exit;
}

$formattedProducts = [];
// Imagem e link de fallback caso o produto não esteja no seu mapeamento
$defaultImage = 'https://placehold.co/600x400/1a1a1a/FFA500?text=PRODUTO';
$defaultLink = '#';

foreach ($hotmartData['items'] as $product) {
    $productID = $product['id'] ?? null;
    
    // Verifica se o produto está ativo e se temos dados personalizados para ele
    if ($product['status'] === 'ACTIVE' && $productID && isset($productCustomData[$productID])) {
        $customData = $productCustomData[$productID];

        $formattedProducts[] = [
            'nome'      => $product['name'] ?? 'Nome do Produto Desconhecido',
            'descricao' => $customData['descricao_custom'] ?? ($product['format'] ?? 'Produto Hotmart'), // Usa descrição customizada se houver, senão o formato ou genérica
            'imagem'    => $customData['imagem'], // Usa sua imagem personalizada
            'link'      => $customData['link'] // Usa seu link de checkout personalizado
        ];
    }
}

echo json_encode($formattedProducts);

?>