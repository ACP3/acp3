<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers;

use ACP3\Core;
use ACP3\Core\Mailer\MailerMessage;

class SendEmail
{
    public function __construct(private readonly Core\Mailer $mailer)
    {
    }

    /**
     * Generates and sends an E-mail.
     */
    public function execute(
        MailerMessage $message,
    ): bool {
        return $this->mailer
            ->reset()
            ->send($message);
    }
}
