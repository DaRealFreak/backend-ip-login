<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SKeuper\BackendIpLogin\Security\ContextValidation;
use SKeuper\BackendIpLogin\Service\AuthenticationService;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\RouteRedirect;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\RedirectResponse;

class MfaController extends \TYPO3\CMS\Backend\Controller\MfaController
{

    /**
     * Handle the request for the Multi-factor authentication ourselves
     * due to there being no way to prevent the MFA authentication from getting activated.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RouteNotFoundException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        if (ContextValidation::validateContext(true)) {
            $user = $this->getBackendUser()->user ?? [];
            if (AuthenticationService::isAllowedByIP($user)) {
                $this->getBackendUser()->setAndSaveSessionData('mfa', true);
                return new RedirectResponse(
                    $this->uriBuilder->buildUriWithRedirect('login', [], RouteRedirect::createFromRequest($request))
                );
            }
        }

        return parent::handleRequest($request);
    }
}
