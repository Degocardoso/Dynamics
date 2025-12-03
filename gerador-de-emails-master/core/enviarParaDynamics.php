<?php
header('Content-Type: application/json');

// --- 0. CONFIGURAÇÃO ---
$powerAutomateUrl = "https://3115b4da60134c64aff4d5089dca39.59.environment.api.powerplatform.com:443/powerautomate/automations/direct/workflows/9db64fcaed8e442cb9c5af0249a49dea/triggers/manual/paths/invoke?api-version=1&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=9EUWZcJ3CzC4htyRKCFCPS7e7N39rqYqEXvL3I0YlQI"; // Lembre-se de colar sua URL aqui

// --- 1. COLETA DOS DADOS ---
$input = json_decode(file_get_contents('php://input'), true);

$campaignName = $input['nomeCampanha'] ?? '';
$subjectPlainText = $input['assunto'] ?? '';
$sendDate = $input['dataEnvio'] ?? '';
$htmlBodyCompleto = $input['corpoHTML'] ?? '';

// --- 2. VALIDAÇÃO ---
if (empty($powerAutomateUrl) || strpos($powerAutomateUrl, 'http') !== 0) {
    echo json_encode(['success' => false, 'message' => 'A URL do Power Automate não foi configurada.']);
    exit();
}
if (empty($campaignName) || empty($subjectPlainText) || empty($sendDate) || empty($htmlBodyCompleto)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit();
}

// --- 3. EXTRAÇÃO DO CONTEÚDO DO E-MAIL ---
$corpoFinalParaEnvio = $htmlBodyCompleto;
preg_match('/<body[^>]*>([\s\S]*)<\/body>/i', $htmlBodyCompleto, $matches);
if (isset($matches[1])) {
    $corpoFinalParaEnvio = trim($matches[1]);
}

// --- 4. PREPARAÇÃO DOS DADOS PARA ENVIO ---
// Corrigido para <span> e adicionado os novos campos
$subjectHtml = '<span>' . htmlspecialchars($subjectPlainText) . '</span>';

$postData = json_encode([
    'nomeCampanha' => $campaignName,
    'assunto' => $subjectHtml,
    'dataEnvio' => $sendDate, // Data já está no formato ISO 8601
    'corpoHTML' => $corpoFinalParaEnvio
]);

// --- 5. ENVIO E RESPOSTA (sem alterações) ---
$ch = curl_init($powerAutomateUrl);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    $errorMessage = 'Erro de conexão cURL: ' . curl_error($ch);
    curl_close($ch);
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    exit();
}
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['success' => true, 'message' => 'Dados enviados para o fluxo com sucesso!']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'O Power Automate respondeu com um erro. Status: ' . $httpCode
    ]);
}
