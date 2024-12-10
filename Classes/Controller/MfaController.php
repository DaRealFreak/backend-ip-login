<?php

declare(strict_types=1);

namespace SKeuper\BackendIpLogin\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use SKeuper\BackendIpLogin\Security\ContextValidation;
use SKeuper\BackendIpLogin\Service\AuthenticationService;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\RouteRedirect;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\View\AuthenticationStyleInformation;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderRegistry;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Page\PageRenderer;

class MfaController extends \TYPO3\CMS\Backend\Controller\MfaController
{

    /**
     * @param UriBuilder $uriBuilder
     * @param MfaProviderRegistry $mfaProviderRegistry
     * @param ModuleTemplateFactory $moduleTemplateFactory
     * @param AuthenticationStyleInformation $authenticationStyleInformation
     * @param PageRenderer $pageRenderer
     */
    public function __construct(
        UriBuilder            $uriBuilder, MfaProviderRegistry $mfaProviderRegistry,
        ModuleTemplateFactory $moduleTemplateFactory, AuthenticationStyleInformation $authenticationStyleInformation,
        PageRenderer          $pageRenderer
    )
    {
        parent::__construct($uriBuilder, $mfaProviderRegistry, $moduleTemplateFactory, $authenticationStyleInformation, $pageRenderer);
    }

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
        if (ContextValidation::validateContext()) {
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
