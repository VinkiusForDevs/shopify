<?php
/**
 * Shopify OAuth install/callback.
 *
 * @license GPLv2 or later
 */

require_once __DIR__ . '/bootstrap.php';

$action = (string) ($_GET['action'] ?? 'install');
$apiKey = (string) getenv('SHOPIFY_API_KEY');
$apiSecret = (string) getenv('SHOPIFY_API_SECRET');
$appUrl = rtrim((string) getenv('SHOPIFY_APP_URL'), '/');
$scopes = (string) (getenv('SHOPIFY_SCOPES') ?: 'read_products');
$shop = (string) ($_GET['shop'] ?? '');

if ($action === 'logout') {
    $_SESSION = array();
    session_destroy();
    header('Location: /index.php');
    exit;
}

if (!preg_match('/^[a-z0-9][a-z0-9\-]*\.myshopify\.com$/i', $shop)) {
    http_response_code(400);
    echo 'Invalid shop domain.';
    exit;
}

if ($apiKey === '' || $apiSecret === '' || $appUrl === '') {
    http_response_code(500);
    echo 'Set SHOPIFY_API_KEY, SHOPIFY_API_SECRET and SHOPIFY_APP_URL.';
    exit;
}

if ($action === 'install') {
    $state = bin2hex(random_bytes(16));
    $_SESSION['vc_oauth_state'] = $state;
    $_SESSION['vc_oauth_shop'] = $shop;

    $params = array(
        'client_id' => $apiKey,
        'scope' => $scopes,
        'redirect_uri' => $appUrl . '/shopify.php?action=callback',
        'state' => $state,
    );

    header('Location: https://' . $shop . '/admin/oauth/authorize?' . http_build_query($params));
    exit;
}

if ($action === 'callback') {
    $state = (string) ($_GET['state'] ?? '');

    if (empty($_SESSION['vc_oauth_state']) || !hash_equals((string) $_SESSION['vc_oauth_state'], $state)) {
        http_response_code(400);
        echo 'Invalid OAuth state.';
        exit;
    }

    $params = $_GET;
    unset($params['hmac'], $params['signature']);
    ksort($params);
    $pairs = array();
    foreach ($params as $key => $value) {
        $pairs[] = $key . '=' . $value;
    }
    $computed = hash_hmac('sha256', implode('&', $pairs), $apiSecret);

    if (!hash_equals($computed, (string) ($_GET['hmac'] ?? ''))) {
        http_response_code(400);
        echo 'Invalid HMAC.';
        exit;
    }

    $code = (string) ($_GET['code'] ?? '');
    $ch = curl_init('https://' . $shop . '/admin/oauth/access_token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
        'client_id' => $apiKey,
        'client_secret' => $apiSecret,
        'code' => $code,
    )));
    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded) || empty($decoded['access_token'])) {
        http_response_code(500);
        echo 'Token exchange failed.';
        exit;
    }

    $shops = vc_store_get('shops', array());
    if (!is_array($shops)) {
        $shops = array();
    }
    $shops[$shop] = array(
        'access_token' => (string) $decoded['access_token'],
        'scope' => (string) ($decoded['scope'] ?? ''),
        'installed_at' => gmdate('c'),
    );
    vc_store_set('shops', $shops);

    $_SESSION['vc_admin'] = true;
    $_SESSION['vc_shop'] = $shop;
    unset($_SESSION['vc_oauth_state'], $_SESSION['vc_oauth_shop']);

    header('Location: /index.php');
    exit;
}

http_response_code(404);
echo 'Not found.';
