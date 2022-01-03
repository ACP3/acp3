<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation\ValidationRules;

use ACP3\Core\Validation\Validator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ValidationRuleInterface
{
    public function getMessage(): string;

    /**
     * @return $this
     */
    public function setMessage(string $message): self;

    public function validate(Validator $validator, bool|int|float|string|array|UploadedFile|null $data, string|array $field = '', array $extra = []): void;

    public function isValid(bool|int|float|string|array|UploadedFile|null $data, string|array $field = '', array $extra = []): bool;
}
