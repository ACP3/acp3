<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Core\Application\BootstrapCache\Event\Listener;

use ACP3\Core\View\Renderer\Smarty\Filters\MoveToBottom;
use FOS\HttpCache\SymfonyCache\CacheEvent;
use FOS\HttpCache\SymfonyCache\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StaticAssetsListener implements EventSubscriberInterface
{
    const PLACEHOLDER = '</body>';

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::POST_HANDLE => 'postHandle'
        ];
    }

    public function postHandle(CacheEvent $event)
    {
        $response = $event->getResponse();
        $content = $response->getContent();
        if (strpos($content, static::PLACEHOLDER) !== false) {
            $content = str_replace(
                static::PLACEHOLDER,
                $this->addElementsFromTemplates($content) . "\n" . static::PLACEHOLDER,
                $this->getCleanedUpTemplateOutput($content)
            );

            $response->setContent($content);
            $response->headers->set('Content-Length', strlen($content));
        }
    }

    /**
     * @param string $tplOutput
     * @return string
     */
    private function getCleanedUpTemplateOutput(string $tplOutput): string
    {
        return preg_replace(MoveToBottom::ELEMENT_CATCHER_REGEX_PATTERN, '', $tplOutput);
    }

    /**
     * @param string $tplOutput
     * @return string
     */
    private function addElementsFromTemplates(string $tplOutput): string
    {
        $matches = [];
        preg_match_all(MoveToBottom::ELEMENT_CATCHER_REGEX_PATTERN, $tplOutput, $matches);

        return implode("\n", array_unique($matches[1])) . "\n";
    }
}