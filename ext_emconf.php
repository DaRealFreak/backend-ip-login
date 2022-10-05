<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Backend IP Auto Login',
    'description' => 'Remember the login based on the network mask or ip. Only for development, unsafe for live environments!',
    'category' => 'services',
    'author' => 'Steffen Keuper',
    'author_email' => 'steffen.keuper@web.de',
    'state' => 'alpha',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
