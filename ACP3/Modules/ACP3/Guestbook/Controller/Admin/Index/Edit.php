<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Guestbook;

class Edit extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Guestbook\Validation\AdminFormValidation
     */
    protected $adminFormValidation;
    /**
     * @var Guestbook\Model\GuestbookModel
     */
    private $guestbookModel;
    /**
     * @var Core\View\Block\FormBlockInterface
     */
    private $block;

    /**
     * Edit constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param Core\View\Block\FormBlockInterface $block
     * @param Guestbook\Model\GuestbookModel $guestbookModel
     * @param \ACP3\Modules\ACP3\Guestbook\Validation\AdminFormValidation $adminFormValidation
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\View\Block\FormBlockInterface $block,
        Guestbook\Model\GuestbookModel $guestbookModel,
        Guestbook\Validation\AdminFormValidation $adminFormValidation
    ) {
        parent::__construct($context);

        $this->adminFormValidation = $adminFormValidation;
        $this->guestbookModel = $guestbookModel;
        $this->block = $block;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute(int $id)
    {
        $guestbook = $this->guestbookModel->getOneById($id);
        if (empty($guestbook) === false) {
            return $this->block
                ->setRequestData($this->request->getPost()->all())
                ->setData($guestbook)
                ->render();
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executePost($id)
    {
        return $this->actionHelper->handleSaveAction(function () use ($id) {
            $formData = $this->request->getPost()->all();

            $settings = $this->config->getSettings(Guestbook\Installer\Schema::MODULE_NAME);

            $this->adminFormValidation
                ->setSettings($settings)
                ->validate($formData);

            $formData['active'] = $settings['notify'] == 2 ? $formData['active'] : 1;

            $bool = $this->guestbookModel->save($formData, $id);

            return $bool;
        });
    }
}
