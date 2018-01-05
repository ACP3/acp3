<?php

namespace ACP3\Installer\Core\Application;

use ACP3\Core;
use ACP3\Installer\Core\DependencyInjection\ServiceContainerBuilder;
use ACP3\Installer\Core\Environment\ApplicationPath;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Bootstrap extends Core\Application\AbstractBootstrap
{
    /**
     * @var \ACP3\Installer\Core\Environment\ApplicationPath ApplicationPath
     */
    protected $appPath;

    /**
     * @inheritdoc
     */
    public function handle(SymfonyRequest $symfonyRequest, $type = self::MASTER_REQUEST, $catch = true)
    {
        $this->setErrorHandler();
        $this->initializeClasses($symfonyRequest);

        return $this->outputPage();
    }

    /**
     * @inheritdoc
     */
    public function startupChecks()
    {
        // Standardzeitzone festlegen
        date_default_timezone_set('UTC');

        if ($this->appMode === Core\Environment\ApplicationMode::UPDATER) {
            return $this->databaseConfigExists();
        }

        return true;
    }

    protected function initializeApplicationPath()
    {
        $this->appPath = new ApplicationPath($this->appMode);
    }

    /**
     * @inheritdoc
     */
    public function initializeClasses(SymfonyRequest $symfonyRequest)
    {
        $this->container = ServiceContainerBuilder::create($this->logger, $this->appPath, $symfonyRequest, $this->appMode);
    }

    private function applyThemePaths()
    {
        $this->appPath
            ->setDesignPathWeb($this->appPath->getInstallerWebRoot() . 'design/')
            ->setDesignPathInternal('');
    }

    /**
     * @inheritdoc
     */
    public function outputPage()
    {
        $redirect = $this->container->get('core.http.redirect_response');

        try {
            $this->applyThemePaths();

            $response = $this->container->get('core.application.controller_action_dispatcher')->dispatch();
        } catch (Core\Controller\Exception\ControllerActionNotFoundException $e) {
            $response = $redirect->temporary('errors/index/not_found');
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $response = $redirect->temporary('errors/index/server_error');
        }

        return $response;
    }
}
