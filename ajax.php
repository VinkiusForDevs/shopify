<?php
/**
 * Vinkius Connector for Shopify - AJAX bridge.
 *
 * @license GPLv2 or later
 */

require_once __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$raw = (string) ($_GET['route'] ?? '');
$extra = array();

if (strpos($raw, '?') !== false) {
    list($raw, $queryString) = explode('?', $raw, 2);
    parse_str($queryString, $extra);
}

$route = trim($raw, '/');
$public = array('agent-manifest', 'consent', 'consent/revoke');

if (!in_array($route, $public, true) && !vc_is_admin()) {
    http_response_code(403);
    echo json_encode(array('error' => 'forbidden'));
    exit;
}

$map = array(
    'agent-manifest' => 'agent_manifest',
    'consent' => 'consent_create',
    'consent/revoke' => 'consent_revoke',
    'panel/status' => 'panel_status',
    'panel/section' => 'panel_section',
    'panel/capabilities' => 'panel_capabilities',
    'panel/incidents' => 'panel_incidents',
    'panel/briefing' => 'panel_briefing',
    'panel/evidence-packs' => 'panel_evidence_packs',
    'panel/agents-export' => 'panel_agents_export',
    'panel/tokens' => 'panel_tokens',
    'panel/halt' => 'panel_halt',
    'panel/resume' => 'panel_resume',
    'panel/circuit-reset' => 'panel_circuit_reset',
    'panel/regen-secret' => 'panel_regen_secret',
    'panel/connector' => 'panel_connector',
    'panel/evidence-pack/generate' => 'panel_evidence_pack_generate',
    'panel/evidence-pack' => 'panel_evidence_pack',
    'panel/audit' => 'panel_audit',
    'panel/settings' => 'panel_settings',
    'auth/device/init' => 'device_init',
    'auth/device/poll' => 'device_poll',
    'auth/logout' => 'logout',
);

if (!isset($map[$route])) {
    http_response_code(404);
    echo json_encode(array('error' => 'not_found', 'route' => $route));
    exit;
}

$body = array();
$rawBody = file_get_contents('php://input');
$decoded = json_decode($rawBody, true);
if (is_array($decoded)) {
    $body = $decoded;
}

$params = array_merge($_GET, $_POST, $extra, $body);
unset($params['route']);

$req = new \WP_REST_Request();
foreach ($params as $key => $value) {
    $req->set($key, $value);
}

$rest = 'VinkiusConnector\\Rest\\Rest';
$method = $map[$route];

try {
    $result = $rest::$method($req);
    if ($result instanceof \WP_REST_Response) {
        http_response_code($result->status ?: 200);
        echo json_encode($result->data);
    }
    else {
        echo json_encode($result);
    }
}
catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
}
