<?php
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    require __DIR__ . '/../src/OpenIDConnectClient.php';
}

use No1service\OpenIDConnectClient;

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$issuer = getenv('OIDC_ISSUER') ?: 'https://id.provider.com';
$clientId = getenv('OIDC_CLIENT_ID') ?: '';
$clientSecret = getenv('OIDC_CLIENT_SECRET');
$usePkce = filter_var(getenv('OIDC_USE_PKCE') ?: '0', FILTER_VALIDATE_BOOLEAN);
$scopes = trim(getenv('OIDC_SCOPES') ?: 'openid profile email');
$redirectUrl = getenv('OIDC_REDIRECT_URL');

$proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'));
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$callback = $redirectUrl ?: $proto . '://' . $host . '/examples/callback.php';

$oidc = new OpenIDConnectClient($issuer, $clientId !== '' ? $clientId : null, $clientSecret ?: null);
$oidc->setRedirectURL($callback);
$oidc->addScope(preg_split('/\s+/', $scopes));
if ($usePkce) {
    $oidc->setCodeChallengeMethod('S256');
}
$oidc->authenticate();

$_SESSION['tokens'] = [
    'access_token' => $oidc->getAccessToken(),
    'id_token' => $oidc->getIdToken(),
    'refresh_token' => $oidc->getRefreshToken(),
    'claims' => $oidc->getVerifiedClaims()
];

$claims = $_SESSION['tokens']['claims'];
$name = null;
if (is_object($claims) && property_exists($claims, 'name')) {
    $name = $claims->name;
} elseif (is_object($claims) && property_exists($claims, 'given_name')) {
    $name = $claims->given_name;
}
if (!$name) {
    $name = $oidc->requestUserInfo('given_name') ?: $oidc->requestUserInfo('name');
}

echo '<html><head><title>Callback</title></head><body>';
echo '<div>Logged in as ' . htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8') . '</div>';
echo '</body></html>';