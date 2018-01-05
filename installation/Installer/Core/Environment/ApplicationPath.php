<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Installer\Core\Environment;

class ApplicationPath extends \ACP3\Core\Environment\ApplicationPath
{
    /**
     * @var string
     */
    private $installerAppDir;
    /**
     * @var string
     */
    private $installerCacheDir;
    /**
     * @var string
     */
    private $installerClassesDir;
    /**
     * @var string
     */
    private $installerModulesDir;
    /**
     * @var string
     */
    private $installerWebRoot;

    /**
     * ApplicationPath constructor.
     *
     * @param string $applicationMode
     */
    public function __construct(string $applicationMode)
    {
        parent::__construct($applicationMode);

        $this->installerWebRoot = $this->getWebRoot();
        $this->setWebRoot(\substr($this->getWebRoot() !== '/' ? $this->getWebRoot() . '/' : '/', 0, -14));
        $this->installerAppDir = \realpath($this->getAppDir() . '../installation') . '/Installer/';
        $this->installerModulesDir = $this->installerAppDir . 'Modules/';
        $this->installerClassesDir = $this->installerAppDir . 'Core/';
        $this->setDesignRootPathInternal(ACP3_ROOT_DIR . 'installation/design/');
    }

    /**
     * @return string
     */
    public function getInstallerAppDir()
    {
        return $this->installerAppDir;
    }

    /**
     * @param string $installerAppDir
     *
     * @return ApplicationPath
     */
    public function setInstallerAppDir($installerAppDir)
    {
        $this->installerAppDir = $installerAppDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstallerCacheDir()
    {
        return $this->installerCacheDir;
    }

    /**
     * @param string $installerCacheDir
     *
     * @return ApplicationPath
     */
    public function setInstallerCacheDir($installerCacheDir)
    {
        $this->installerCacheDir = $installerCacheDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstallerClassesDir()
    {
        return $this->installerClassesDir;
    }

    /**
     * @param string $installerClassesDir
     *
     * @return ApplicationPath
     */
    public function setInstallerClassesDir($installerClassesDir)
    {
        $this->installerClassesDir = $installerClassesDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstallerModulesDir()
    {
        return $this->installerModulesDir;
    }

    /**
     * @param string $installerModulesDir
     *
     * @return ApplicationPath
     */
    public function setInstallerModulesDir($installerModulesDir)
    {
        $this->installerModulesDir = $installerModulesDir;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstallerWebRoot()
    {
        return $this->installerWebRoot;
    }

    /**
     * @param string $installerWebRoot
     *
     * @return ApplicationPath
     */
    public function setInstallerWebRoot($installerWebRoot)
    {
        $this->installerWebRoot = $installerWebRoot;

        return $this;
    }
}
