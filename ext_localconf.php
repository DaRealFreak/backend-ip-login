<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Register base authentication service
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    $_EXTKEY,
    'auth',
    \SKeuper\BackendIpLogin\Service\AuthenticationService::class,
    array(
        'title' => 'User authentication',
        'description' => 'Authentication based on the saved ip/network address',
        'subtype' => 'getUserBE,authUserBE',
        'available' => TRUE,
        'priority' => 80,
        'quality' => 80,
        'os' => '',
        'exec' => '',
        'className' => \SKeuper\BackendIpLogin\Service\AuthenticationService::class
    )
);

\SKeuper\BackendIpLogin\Hook\PageRendererHook::register();