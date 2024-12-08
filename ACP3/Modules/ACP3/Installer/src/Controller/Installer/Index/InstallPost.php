<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Controller\Installer\Index;

use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Helpers\Alerts;
use ACP3\Core\Validation\Exceptions\ValidationFailedException;
use ACP3\Modules\ACP3\Installer\Core\Environment\ApplicationPath;
use ACP3\Modules\ACP3\Installer\Helpers\Navigation;
use ACP3\Modules\ACP3\Installer\Model\InstallModel;
use ACP3\Modules\ACP3\Installer\Validation\FormValidation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class InstallPost extends AbstractAction
{
    public function __construct(
        Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly LoggerInterface $logger,
        private readonly Alerts $alertsHelper,
        Navigation $navigation,
        private readonly InstallModel $installModel,
        private readonly FormValidation $formValidation,
    ) {
        parent::__construct($context, $navigation);
    }

    /**
     * @return array<string, mixed>|JsonResponse|null
     */
    public function __invoke(): array|JsonResponse|null
    {
        try {
            $formData = $this->request->getPost()->all();

            $configFilePath = $this->applicationPath->getAppDir() . 'config.yml';

            $this->formValidation
                ->withConfigFilePath($configFilePath)
                ->validate($formData);

            $this->installModel->writeConfigFile($configFilePath, $formData);
            $this->installModel->updateContainer();
            $this->installModel->installModules();
            $this->installModel->installAclResources();
            $this->installModel->createSuperUser($formData);

            if (isset($formData['sample_data']) && $formData['sample_data'] == 1) {
                $this->installModel->installSampleData();
            }

            $this->installModel->configureModules($formData);

            $this->navigation->markStepComplete('index_install');

            $this->setTemplate('Installer/Installer/index.install.result.tpl');
        } catch (ValidationFailedException $e) {
            return $this->renderErrorBoxOnFailedFormValidation($e);
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->setTemplate('Installer/Installer/index.install.error.tpl');
        }

        return null;
    }

    /**
     * @return array<string, mixed>|Response
     */
    private function renderErrorBoxOnFailedFormValidation(\Exception $exception): array|Response
    {
        $errors = $this->alertsHelper->errorBox($exception->getMessage());
        if ($this->request->isXmlHttpRequest()) {
            return new Response($errors, 400);
        }

        return ['error_msg' => $errors];
    }
}
