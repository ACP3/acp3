<?php
/**
 * Index
 *
 * @author Tino Goratsch
 */

define('ACP3_ROOT_DIR', realpath(__DIR__) . '/');

require './vendor/autoload.php';

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$appMode = \ACP3\Core\Environment\ApplicationMode::PRODUCTION;
if (getenv('ACP3_APPLICATION_MODE') === \ACP3\Core\Environment\ApplicationMode::DEVELOPMENT) {
    $appMode = \ACP3\Core\Environment\ApplicationMode::DEVELOPMENT;
}

$kernel = new \ACP3\Core\Application\Bootstrap($appMode);

if (!$kernel->startupChecks()) {
    echo <<<HTML
The ACP3 is not correctly installed.
Please navigate to the <a href="{$request->getBasePath()}/installation/">installation wizard</a>
and follow its instructions.
HTML;
    exit;
}

$cacheStore = new \Symfony\Component\HttpKernel\HttpCache\Store(
    $kernel->getAppPath()->getCacheDir() . 'http/'
);

$appCache = new \ACP3\Core\Application\BootstrapCache(
    $kernel,
    $cacheStore,
    new \ACP3\Core\Application\BootstrapCache\Esi(),
    ['debug' => $appMode === \ACP3\Core\Environment\ApplicationMode::DEVELOPMENT]
);

$appCache->handle($request)->send();
