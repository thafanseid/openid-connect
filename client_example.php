<?php

/**
 *
 * Copyright MITRE 2012
 *
 * OpenIDConnectClient for PHP5
 * Author: Michael Jett <mjett@mitre.org>
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 */

require __DIR__ . '/vendor/autoload.php';

use No1service\OpenIDConnectClient;

$issuer = getenv('OIDC_ISSUER') ?: 'https://id.provider.com';
$clientId = getenv('OIDC_CLIENT_ID') ?: '';
$clientSecret = getenv('OIDC_CLIENT_SECRET');
$usePkce = filter_var(getenv('OIDC_USE_PKCE') ?: '0', FILTER_VALIDATE_BOOLEAN);
$scopes = trim(getenv('OIDC_SCOPES') ?: 'openid profile email');
$redirectUrl = getenv('OIDC_REDIRECT_URL');

$proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'));
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = $_SERVER['PHP_SELF'] ?? '/client_example.php';
$callback = $redirectUrl ?: $proto . '://' . $host . $path;

$oidc = new OpenIDConnectClient($issuer, $clientId !== '' ? $clientId : null, $clientSecret ?: null);
$oidc->setRedirectURL($callback);
$oidc->addScope(preg_split('/\s+/', $scopes));
if ($usePkce) {
    $oidc->setCodeChallengeMethod('S256');
}
$oidc->authenticate();
$name = $oidc->requestUserInfo('given_name');

?>

<html>
<head>
    <title>Example OpenID Connect Client Use</title>
    <style>
        body {
            font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
        }
    </style>
</head>
<body>

    <div>
        Hello <?php echo htmlspecialchars((string)$name, ENT_QUOTES, 'UTF-8'); ?>
    </div>

</body>
</html>

