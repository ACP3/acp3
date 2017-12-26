<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\Users;

/**
 * Class ViewProfile
 * @package ACP3\Modules\ACP3\Users\Controller\Frontend\Index
 */
class ViewProfile extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Users\Model\Repository\UserRepository
     */
    protected $userRepository;

    /**
     * ViewProfile constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Modules\ACP3\Users\Model\Repository\UserRepository $userRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Users\Model\Repository\UserRepository $userRepository
    ) {
        parent::__construct($context);

        $this->userRepository = $userRepository;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        if ($this->userRepository->resultExists($id) === true) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            $user = $this->user->getUserInfo($id);
            $user['gender'] = str_replace(
                [1, 2, 3],
                ['', $this->translator->t('users', 'female'), $this->translator->t('users', 'male')],
                $user['gender']
            );

            return [
                'user' => $user
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
