<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {
        // Register base authentication service
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
            'backend_ip_login',
            'auth',
            \SKeuper\BackendIpLogin\Service\AuthenticationService::class,
            [
                'title' => 'User authentication',
                'description' => 'Authentication based on the saved ip/network address',
                'subtype' => 'getUserBE,authUserBE',
                'available' => true,
                'priority' => 80,
                'quality' => 80,
                'os' => '',
                'exec' => '',
                'className' => \SKeuper\BackendIpLogin\Service\AuthenticationService::class
            ]
        );

        // register page renderer hook to display during the login page
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
            = \SKeuper\BackendIpLogin\Hook\PageRendererHook::class . '->pageRendererPreProcessHook';
    }
);
