<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers. See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;

/**
 * Class Create
 * @package ACP3\Modules\ACP3\Comments\Controller\Frontend\Index
 */
class Create extends AbstractFrontendAction
{
    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\Comments\Model\Repository\CommentRepository
     */
    protected $commentRepository;
    /**
     * @var \ACP3\Modules\ACP3\Comments\Validation\FormValidation
     */
    protected $formValidation;

    /**
     * @param \ACP3\Core\Controller\Context\FrontendContext         $context
     * @param \ACP3\Core\Date                                       $date
     * @param \ACP3\Modules\ACP3\Comments\Model\Repository\CommentRepository   $commentRepository
     * @param \ACP3\Modules\ACP3\Comments\Validation\FormValidation $formValidation
     * @param \ACP3\Core\Helpers\FormToken                          $formTokenHelper
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        Comments\Model\Repository\CommentRepository $commentRepository,
        Comments\Validation\FormValidation $formValidation,
        Core\Helpers\FormToken $formTokenHelper)
    {
        parent::__construct($context);

        $this->date = $date;
        $this->commentRepository = $commentRepository;
        $this->formValidation = $formValidation;
        $this->formTokenHelper = $formTokenHelper;
    }

    /**
     * @param string $module
     * @param int    $entryId
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute($module, $entryId)
    {
        if ($this->request->getPost()->count() !== 0) {
            return $this->executePost($this->request->getPost()->all(), $module, $entryId);
        }

        return [
            'form' => array_merge($this->fetchFormDefaults(), $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken(),
            'can_use_emoticons' => $this->emoticonsActive === true
        ];
    }

    /**
     * @param array  $formData
     * @param string $module
     * @param int    $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData, $module, $entryId)
    {
        return $this->actionHelper->handlePostAction(
            function () use ($formData, $module, $entryId) {
                $ip = $this->request->getServer()->get('REMOTE_ADDR', '');

                $this->formValidation
                    ->setIpAddress($ip)
                    ->validate($formData);

                $insertValues = [
                    'id' => '',
                    'date' => $this->date->toSQL(),
                    'ip' => $ip,
                    'name' => $this->get('core.helpers.secure')->strEncode($formData['name']),
                    'user_id' => $this->user->isAuthenticated() === true ? $this->user->getUserId() : null,
                    'message' => $this->get('core.helpers.secure')->strEncode($formData['message']),
                    'module_id' => $this->modules->getModuleId($module),
                    'entry_id' => $entryId,
                ];

                $bool = $this->commentRepository->insert($insertValues);

                Core\Cache\Purge::doPurge($this->appPath->getCacheDir() . 'http');

                return $this->redirectMessages()->setMessage(
                    $bool,
                    $this->translator->t('system', $bool !== false ? 'create_success' : 'create_error'),
                    $this->request->getQuery()
                );
            }
        );
    }

    /**
     * @return array
     */
    private function fetchFormDefaults()
    {
        $defaults = [
            'name' => '',
            'name_disabled' => false,
            'message' => ''
        ];

        if ($this->user->isAuthenticated() === true) {
            $user = $this->user->getUserInfo();
            $defaults['name'] = $user['nickname'];
            $defaults['name_disabled'] = true;
            $defaults['message'] = '';
        }
        return $defaults;
    }
}
