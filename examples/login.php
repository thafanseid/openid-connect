<?php
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    require __DIR__ . '/../src/OpenIDConnectClient.php';
}

use No1service\OpenIDConnectClient;

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