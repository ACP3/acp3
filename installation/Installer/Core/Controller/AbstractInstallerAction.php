<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Installer\Core\Controller;

use ACP3\Core\Controller\ActionInterface;
use ACP3\Core\Controller\DisplayActionTrait;
use ACP3\Core\Http\RedirectResponse;
use ACP3\Core\I18n\ExtractFromPathTrait;
use Fisharebest\Localization\Locale;

/**
 * Module Controller of the installer modules
 */
abstract class AbstractInstallerAction implements ActionInterface
{
    use ExtractFromPathTrait;
    use DisplayActionTrait;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * @var \ACP3\Installer\Core\I18n\Translator
     */
    protected $translator;
    /**
     * @var \ACP3\Core\Router\RouterInterface
     */
    protected $router;
    /**
     * @var \ACP3\Installer\Core\Environment\ApplicationPath
     */
    protected $appPath;
    /**
     * @var \ACP3\Core\Http\RequestInterface
     */
    protected $request;
    /**
     * @var \ACP3\Core\View
     */
    protected $view;
    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;
    /**
     * @var string
     */
    private $layout = 'layout.tpl';

    /**
     * @param \ACP3\Installer\Core\Controller\Context\InstallerContext $context
     */
    public function __construct(Context\InstallerContext $context)
    {
        $this->container = $context->getContainer();
        $this->translator = $context->getTranslator();
        $this->request = $context->getRequest();
        $this->router = $context->getRouter();
        $this->view = $context->getView();
        $this->response = $context->getResponse();
        $this->appPath = $context->getAppPath();
    }

    /**
     * @inheritdoc
     */
    public function preDispatch()
    {
        $this->setLanguage();

        // Einige Template Variablen setzen
        $this->view->assign('LANGUAGES', $this->languagesDropdown($this->translator->getLocale()));
        $this->view->assign('PHP_SELF', $this->appPath->getPhpSelf());
        $this->view->assign('REQUEST_URI', $this->request->getServer()->get('REQUEST_URI'));
        $this->view->assign('ROOT_DIR', $this->appPath->getWebRoot());
        $this->view->assign('INSTALLER_ROOT_DIR', $this->appPath->getInstallerWebRoot());
        $this->view->assign('DESIGN_PATH', $this->appPath->getDesignPathWeb());
        $this->view->assign('UA_IS_MOBILE', $this->request->getUserAgent()->isMobileBrowser());
        $this->view->assign('IS_AJAX', $this->request->isXmlHttpRequest());

        $languageInfo = \simplexml_load_file(
            $this->appPath->getInstallerModulesDir() . 'Install/Resources/i18n/' . $this->translator->getLocale() . '.xml'
        );
        $this->view->assign(
            'LANG_DIRECTION',
            isset($languageInfo->info->direction) ? $languageInfo->info->direction : 'ltr'
        );
        $this->view->assign('LANG', $this->translator->getShortIsoCode());
    }

    /**
     * @return RedirectResponse
     */
    public function redirect()
    {
        return $this->get('core.http.redirect_response');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * @return \ACP3\Core\View
     */
    protected function getView()
    {
        return $this->view;
    }

    /**
     * @return bool
     */
    protected function getNoOutput()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function get($serviceId)
    {
        return $this->container->get($serviceId);
    }

    /**
     * Generiert das Dropdown-Menü mit den zur Verfügung stehenden Installersprachen
     *
     * @param string $selectedLanguage
     *
     * @return array
     */
    private function languagesDropdown($selectedLanguage)
    {
        $languages = [];
        $paths = \glob($this->appPath->getInstallerModulesDir() . 'Install/Resources/i18n/*.xml');

        foreach ($paths as $file) {
            try {
                $isoCode = $this->getLanguagePackIsoCode($file);
                $locale = Locale::create($isoCode);

                $languages[] = [
                    'language' => $isoCode,
                    'selected' => $selectedLanguage === $isoCode ? ' selected="selected"' : '',
                    'name' => $locale->endonym(),
                ];
            } catch (\DomainException $e) {
            }
        }

        return $languages;
    }

    /**
     * @inheritdoc
     */
    protected function applyTemplateAutomatically()
    {
        return $this->request->getModule() . '/' . $this->request->getController() . '.' . $this->request->getAction() . '.tpl';
    }

    /**
     * @inheritdoc
     */
    protected function addCustomTemplateVarsBeforeOutput()
    {
        $this->view->assign('PAGE_TITLE', $this->translator->t('install', 'acp3_installation'));
        $this->view->assign(
            'TITLE',
            $this->translator->t(
            $this->request->getModule(),
            $this->request->getController() . '_' . $this->request->getAction()
        )
        );
        $this->view->assign('LAYOUT', $this->request->isXmlHttpRequest() ? 'layout.ajax.tpl' : $this->getLayout());
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    private function setLanguage()
    {
        $cookieLocale = $this->request->getCookies()->get('ACP3_INSTALLER_LANG', '');
        if (!\preg_match('=/=', $cookieLocale)
            && \is_file($this->appPath->getInstallerModulesDir() . 'Install/Resources/i18n/' . $cookieLocale . '.xml') === true
        ) {
            $language = $cookieLocale;
        } else {
            $language = 'en_US'; // Fallback language

            foreach ($this->request->getUserAgent()->parseAcceptLanguage() as $locale => $val) {
                $locale = \str_replace('-', '_', $locale);
                if ($this->translator->languagePackExists($locale) === true) {
                    $language = $locale;

                    break;
                }
            }
        }

        $this->translator->setLocale($language);
    }
}
