<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation;

use ACP3\Core\Validation\Event\FormValidationEvent;
use ACP3\Core\Validation\Exceptions\ValidationFailedException;
use ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException;
use ACP3\Core\Validation\ValidationRules\ValidationRuleInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Validator
{
    /**
     * @var array<string|int, string>
     */
    private array $errors = [];
    /**
     * @var array{rule: class-string<ValidationRuleInterface>, params: array<string, mixed>}[]
     */
    private array $constraints = [];

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher, private readonly ContainerInterface $container)
    {
    }

    /**
     * @param mixed[] $params
     *
     * @return static
     */
    public function addConstraint(string $validationRule, array $params = []): self
    {
        $this->constraints[] = [
            'rule' => $validationRule,
            'params' => array_merge($this->getDefaultConstraintParams(), $params),
        ];

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaultConstraintParams(): array
    {
        return [
            'data' => null,
            'field' => '',
            'message' => '',
            'extra' => [],
        ];
    }

    /**
     * @param string[]|string|null $field
     *
     * @return static
     */
    public function addError(string $message, array|string|null $field = ''): self
    {
        if (!empty($field)) {
            $fieldName = $this->mapField($field);
            $this->errors[$fieldName] = $message;
        } else {
            $this->errors[] = $message;
        }

        return $this;
    }

    /**
     * @param string[]|string $field
     */
    private function mapField(array|string $field): string
    {
        if (\is_array($field)) {
            $field = reset($field);
        }

        return str_replace('_', '-', (string) $field);
    }

    /**
     * @param string|class-string<FormValidationEvent> $eventName
     * @param mixed[]                                  $formData
     * @param mixed[]                                  $extra
     */
    public function dispatchValidationEvent(string $eventName, array $formData, array $extra = []): void
    {
        if (class_exists($eventName)) {
            $this->eventDispatcher->dispatch(new $eventName($this, $formData, $extra));
        } else {
            $this->eventDispatcher->dispatch(new FormValidationEvent($this, $formData, $extra), $eventName);
        }
    }

    /**
     * Validates a form.
     *
     * As the validator (currently) holds some state, we also need to take care of that.
     * That's why we empty out the form errors and the constraints at the beginning and end
     * of this method.
     *
     * @throws ValidationFailedException
     * @throws ValidationRuleNotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function validate(): void
    {
        $this->errors = [];

        foreach ($this->constraints as $constraint) {
            if (!$this->container->has($constraint['rule'])) {
                throw new ValidationRuleNotFoundException(\sprintf($this->getExceptionMessage(), $constraint['rule']));
            }

            /** @var ValidationRuleInterface $validationRule */
            $validationRule = $this->container->get($constraint['rule']);
            $params = $constraint['params'];

            if (!empty($params['message'])) {
                $validationRule->setMessage($params['message']);
            }

            $validationRule->validate(
                $this,
                $params['data'],
                $params['field'],
                $params['extra']
            );
        }

        $this->constraints = [];

        if ($this->hasErrors()) {
            throw new ValidationFailedException($this->errors);
        }
    }

    private function getExceptionMessage(): string
    {
        return 'Can not find the validation rule with the name "%s".';
    }

    private function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * @param class-string<ValidationRuleInterface> $validationRule
     *
     * @throws ValidationRuleNotFoundException
     */
    public function is(string $validationRule, mixed $field): bool
    {
        if ($this->container->has($validationRule)) {
            return $this->container->get($validationRule)->isValid($field);
        }

        throw new ValidationRuleNotFoundException(\sprintf($this->getExceptionMessage(), $validationRule));
    }
}
