<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Captcha\Controller\Frontend\Index;

use ACP3\Core;
use Symfony\Component\HttpFoundation\Session\Session;

class Image extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $sessionHandler;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Session $sessionHandler
    ) {
        parent::__construct($context);

        $this->sessionHandler = $sessionHandler;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function execute(string $path)
    {
        $this->response->headers->set('Content-type', 'image/gif');
        $this->response->headers->addCacheControlDirective('no-cache', true);
        $this->response->headers->addCacheControlDirective('must-revalidate', true);
        $this->response->headers->add(['Expires' => 'Mon, 26 Jul 1997 05:00:00 GMT']);

        if ($this->sessionHandler->has('captcha_' . $path)) {
            $this->generateCaptcha($this->sessionHandler->get('captcha_' . $path));
        }

        return $this->response;
    }

    protected function generateCaptcha(string $captchaText)
    {
        $captchaLength = \strlen($captchaText);
        $width = $captchaLength * 25;
        $height = 30;

        \ob_start();

        $image = \imagecreate($width, $height);

        // Background color
        \imagecolorallocate($image, 255, 255, 255);

        $textColor = \imagecolorallocate($image, 0, 0, 0);

        for ($i = 0; $i < $captchaLength; ++$i) {
            $font = \mt_rand(2, 5);
            $posLeft = 22 * $i + 10;
            $posTop = \mt_rand(1, $height - \imagefontheight($font) - 3);
            \imagestring($image, $font, $posLeft, $posTop, $captchaText[$i], $textColor);
        }

        \imagegif($image);
        \imagedestroy($image);

        $this->response->setContent(\ob_get_contents());

        \ob_end_clean();
    }
}
