<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Backend IP Auto Login',
    'description' => 'Remember the login based on the network mask or ip. Only for development, unsafe for live environments!',
    'category' => 'services',
    'author' => 'Steffen Keuper',
    'author_email' => 'steffen.keuper@web.de',
    'state' => 'alpha',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.6',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
