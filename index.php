<?php
/**
 * Vinkius Connector for Shopify - governance panel.
 *
 * @license GPLv2 or later
 */

require_once __DIR__ . '/bootstrap.php';

$screens = array(
    'connect' => 'VinkiusConnector\\Admin\\ScreenConnect',
    'mission-control' => 'VinkiusConnector\\Admin\\ScreenMissionControl',
    'agent-activity' => 'VinkiusConnector\\Admin\\ScreenAgentActivity',
    'connector-traffic' => 'VinkiusConnector\\Admin\\ScreenConnectorTraffic',
    'access-tokens' => 'VinkiusConnector\\Admin\\ScreenAccessTokens',
    'agent-passport' => 'VinkiusConnector\\Admin\\ScreenAgentPassport',
    'ai-spend' => 'VinkiusConnector\\Admin\\ScreenAiSpend',
    'capability-reliability' => 'VinkiusConnector\\Admin\\ScreenCapabilityReliability',
    'security-posture' => 'VinkiusConnector\\Admin\\ScreenSecurityPosture',
    'request-failures' => 'VinkiusConnector\\Admin\\ScreenRequestFailures',
    'compliance' => 'VinkiusConnector\\Admin\\ScreenCompliance',
    'incidents' => 'VinkiusConnector\\Admin\\ScreenIncidents',
    'consent' => 'VinkiusConnector\\Admin\\ScreenConsent',
    'settings' => 'VinkiusConnector\\Admin\\ScreenSettings',
);

$labels = array(
    'connect' => 'AI Connections',
    'mission-control' => 'Mission Control',
    'agent-activity' => 'Agent Activity',
    'connector-traffic' => 'Connector Traffic',
    'access-tokens' => 'Access Tokens',
    'agent-passport' => 'Agent Passport',
    'ai-spend' => 'AI Spend',
    'capability-reliability' => 'Capability Reliability',
    'security-posture' => 'Security Posture',
    'request-failures' => 'Request Failures',
    'compliance' => 'Compliance & Evidence',
    'incidents' => 'Incidents',
    'consent' => 'Consent',
    'settings' => 'Settings',
);

// Shopify OAuth install entry point.
if (!vc_is_admin() && getenv('SHOPIFY_API_KEY') && isset($_GET['shop'])) {
    header('Location: /shopify.php?action=install&shop=' . rawurlencode((string) $_GET['shop']));
    exit;
}

// Password login (used when the app is not installed through Shopify OAuth).
if (!vc_is_admin()) {
    $error = '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['vc_password'])) {
        $expected = (string) getenv('VC_ADMIN_PASSWORD');
        if ($expected !== '' && hash_equals($expected, (string) $_POST['vc_password'])) {
            $_SESSION['vc_admin'] = true;
            $_SESSION['vc_shop'] = 'Shopify store';
            header('Location: /index.php');
            exit;
        }
        $error = 'Invalid password.';
    }

    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Vinkius Connector</title>';
    echo '<link rel="stylesheet" href="/admin/css/vinkius-connector.css"></head><body>';
    echo '<div class="wrap vc-wrap" style="max-width:420px;margin:80px auto;">';
    echo '<h1 class="vc-title">Vinkius Connector</h1>';
    if (getenv('VC_ADMIN_PASSWORD') === '') {
        echo '<p>Set the <code>VC_ADMIN_PASSWORD</code> environment variable, or install the app through Shopify OAuth.</p>';
    }
    else {
        if ($error !== '') { echo '<p style="color:#b32d2e;">' . esc_html($error) . '</p>'; }
        echo '<form method="post"><p><label for="vc_password">Admin password</label>';
        echo '<input type="password" id="vc_password" name="vc_password" class="regular-text" autofocus /></p>';
        echo '<p><button type="submit" class="button button-primary">Sign in</button></p></form>';
    }
    echo '</div></body></html>';
    exit;
}

// Settings POST.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'vc_save_settings') {
    \VinkiusConnector\Admin\ScreenSettings::save_settings();
}

$section = preg_replace('/[^a-z0-9\-]/', '', (string) ($_GET['section'] ?? 'connect'));
if (!isset($screens[$section])) {
    $section = 'connect';
}

$config = array(
    'restUrl' => '/ajax.php?route=',
    'restNonce' => $_SESSION['vc_nonce'],
    'links' => \VinkiusConnector\Links::all(),
    'gov' => array(
        'capabilityBase' => \VinkiusConnector\Links::capability(''),
        'upgrade' => \VinkiusConnector\Links::cloud('upgrade'),
    ),
    'labels' => array(),
);

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vinkius AI Governance</title>
    <link rel="stylesheet" href="/admin/css/vinkius-connector.css">
    <link rel="stylesheet" href="/admin/css/vc-governance.css">
    <script>window.VCConfig = <?php echo json_encode($config); ?>;</script>
    <script src="/admin/js/vinkius-connector.js"></script>
    <script src="/admin/js/vendor/echarts.min.js"></script>
    <script src="/admin/js/vc-charts.js"></script>
    <script src="/admin/js/vc-governance.js"></script>
</head>
<body>
    <nav class="vc-nav" style="margin:10px 0;display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach ($screens as $slug => $class): ?>
            <a style="<?php echo $slug === $section ? 'font-weight:700;' : ''; ?>" href="/index.php?section=<?php echo rawurlencode($slug); ?>">
                <?php echo esc_html(isset($labels[$slug]) ? $labels[$slug] : $slug); ?>
            </a>
        <?php endforeach; ?>
        <a href="/logout.php" style="margin-left:auto;">Sign out</a>
    </nav>
    <?php $screens[$section]::render_page(); ?>
</body>
</html>
