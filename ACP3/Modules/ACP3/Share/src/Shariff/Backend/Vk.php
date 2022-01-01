<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Shariff\Backend;

/**
 * Class Vk.
 */
class Vk extends Request implements ServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vk';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest($url)
    {
        return new \GuzzleHttp\Psr7\Request(
            'GET',
            'https://vk.com/share.php?act=count&index=1&url=' . urlencode($url)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filterResponse($content)
    {
        // 'VK.Share.count(1, x);' with x being the count
        $strCount = mb_substr($content, 18, mb_strlen($content) - 20);

        return $strCount ? '{"count": ' . $strCount . '}' : '';
    }

    /**
     * {@inheritdoc}
     */
    public function extractCount(array $data)
    {
        return $data['count'] ?? 0;
    }
}
