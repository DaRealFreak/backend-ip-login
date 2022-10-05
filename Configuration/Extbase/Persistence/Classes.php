<?php
declare(strict_types=1);

return [
    \TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class => [
        'subclasses' => [\SKeuper\BackendIpLogin\Domain\Model\BackendUser::class],
    ],
    \SKeuper\BackendIpLogin\Domain\Model\BackendUser::class => [
        'recordType' => '0',
        'tableName' => 'be_users',
    ],
];