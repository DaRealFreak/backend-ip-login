<?php

use SKeuper\BackendIpLogin\Controller\MfaController;

return [
    // REALLY not a fan of this, but it's the only way to hook into the MFA process
    // we are overriding the MFA controller route to use our own controller instead
    //
    // not documented, but routes are possible to get overridden based on loading order
    // and since the core is loaded first, we can override the route here
    'auth_mfa' => [
        'path' => '/auth/mfa',
        'target' => MfaController::class . '::handleRequest',
    ],
];
