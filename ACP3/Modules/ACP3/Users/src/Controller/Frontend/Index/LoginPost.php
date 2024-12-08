<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core\Authentication\Exception\AuthenticationException;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Helpers\FormAction;
use ACP3\Core\Helpers\Secure;
use ACP3\Core\Http\RedirectResponse;
use ACP3\Modules\ACP3\Users;
use ACP3\Modules\ACP3\Users\Model\AuthenticationModel;
use Symfony\Component\HttpFoundation\Response;

class LoginPost extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly FormAction $actionHelper,
        private readonly RedirectResponse $redirectResponse,
        private readonly Secure $secureHelper,
        private readonly AuthenticationModel $authenticationModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): Response
    {
        try {
            $this->authenticationModel->login(
                $this->secureHelper->strEncode($this->request->getPost()->get('nickname', '')),
                $this->request->getPost()->get('pwd', ''),
                $this->request->getPost()->has('remember')
            );

            if ($this->request->getParameters()->has('redirect')) {
                return $this->redirectResponse->temporary(
                    base64_decode((string) $this->request->getParameters()->get('redirect'))
                );
            }

            return $this->redirectResponse->toNewPage($this->applicationPath->getWebRoot());
        } catch (Users\Exception\LoginFailedException) {
            $phrase = $this->translator->t('users', 'nickname_or_password_wrong');
        } catch (Users\Exception\UserAccountLockedException) {
            $phrase = $this->translator->t('users', 'account_locked');
        }

        $localizedException = new AuthenticationException($phrase);

        return $this->actionHelper->renderErrorBoxOnFailedFormValidation($localizedException);
    }
}
