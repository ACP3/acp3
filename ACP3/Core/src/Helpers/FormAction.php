<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Helpers;

use ACP3\Core;
use Doctrine\DBAL\ConnectionException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class FormAction
{
    public function __construct(private Core\Database\Connection $db, private Core\I18n\Translator $translator, private Core\Http\RequestInterface $request, private Core\Router\RouterInterface $router, private Core\Helpers\Alerts $alerts, private Core\Helpers\RedirectMessages $redirectMessages)
    {
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function handlePostAction(callable $callback, ?string $path = null): array|JsonResponse|RedirectResponse|string
    {
        try {
            $this->db->beginTransaction();

            $result = $callback();

            $this->db->commit();

            return $result;
        } catch (Core\Validation\Exceptions\InvalidFormTokenException $e) {
            $this->db->rollBack();

            return $this->redirectMessages->setMessage(
                false,
                $this->translator->t('system', 'form_already_submitted'),
                $path
            );
        } catch (Core\Validation\Exceptions\ValidationFailedException $e) {
            $this->db->rollBack();

            return $this->renderErrorBoxOnFailedFormValidation($e);
        } catch (ConnectionException $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    public function renderErrorBoxOnFailedFormValidation(\Exception $exception): Response
    {
        $errors = $this->alerts->errorBox($exception->getMessage());

        return new Response($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function handleDeleteAction(
        ?string $action,
        callable $callback,
        ?string $moduleConfirmUrl = null,
        ?string $moduleIndexUrl = null
    ): array|JsonResponse|RedirectResponse {
        return $this->handleCustomDeleteAction(
            $action,
            function (array $items) use ($callback, $moduleIndexUrl) {
                $result = $callback($items);

                return $this->prepareRedirectMessageAfterPost($result, 'delete', $moduleIndexUrl);
            },
            $moduleConfirmUrl,
            $moduleIndexUrl
        );
    }

    public function handleCustomDeleteAction(
        ?string $action,
        callable $callback,
        ?string $moduleConfirmUrl = null,
        ?string $moduleIndexUrl = null
    ): array|JsonResponse|RedirectResponse|Response {
        [$moduleConfirmUrl, $moduleIndexUrl] = $this->generateDefaultConfirmationBoxUris(
            $moduleConfirmUrl,
            $moduleIndexUrl
        );
        $result = $this->deleteItem($action, $moduleConfirmUrl, $moduleIndexUrl);

        if ($result instanceof Response) {
            return $result;
        }

        if ($action === 'confirmed') {
            return $callback($result);
        }

        return $result;
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function handleDuplicateAction(callable $callback, ?string $path = null): array|JsonResponse|RedirectResponse|string
    {
        return $this->handlePostAction(function () use ($callback, $path) {
            $result = $callback();

            return $this->prepareRedirectMessageAfterPost($result, 'duplicate', $path);
        }, $path);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function handleSettingsPostAction(callable $callback, ?string $path = null): array|JsonResponse|RedirectResponse|string
    {
        return $this->handlePostAction(function () use ($callback, $path) {
            $result = $callback();

            return $this->prepareRedirectMessageAfterPost($result, 'settings', $path);
        }, $path);
    }

    /**
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\Exception
     */
    public function handleSaveAction(callable $callback, ?string $path = null): array|JsonResponse|RedirectResponse|string
    {
        return $this->handlePostAction(function () use ($callback, $path) {
            $result = $callback();

            return $this->prepareRedirectMessageAfterPost($result, 'save', $path);
        });
    }

    private function prepareRedirectMessageAfterPost(bool|int|Response $response, string $phrase, ?string $path = null): JsonResponse|RedirectResponse|Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        return $this->setRedirectMessage(
            $response,
            $this->translator->t('system', $phrase . ($response !== false ? '_success' : '_error')),
            $path
        );
    }

    public function setRedirectMessage(bool|int $result, string $translatedText, ?string $path = null): JsonResponse|RedirectResponse
    {
        return $this->redirectMessages->setMessage(
            $result,
            $translatedText,
            $this->request->getPost()->has('continue') ? $this->request->getPathInfo() : $path
        );
    }

    private function generateDefaultConfirmationBoxUris(?string $moduleConfirmUrl, ?string $moduleIndexUrl): array
    {
        if ($moduleConfirmUrl === null) {
            $moduleConfirmUrl = $this->request->getFullPath();
        }

        if ($moduleIndexUrl === null) {
            $moduleIndexUrl = $this->request->getModuleAndController();
        }

        return [$moduleConfirmUrl, $moduleIndexUrl];
    }

    /**
     * helper function for deleting a result set.
     */
    private function deleteItem(?string $action, ?string $moduleConfirmUrl = null, ?string $moduleIndexUrl = null): array|JsonResponse|RedirectResponse|Response
    {
        $entries = $this->prepareRequestData();

        if (empty($entries)) {
            return $this->redirectMessages->setMessage(
                false,
                $this->translator->t('system', 'no_entries_selected'),
                $moduleIndexUrl
            );
        }

        if ($action !== 'confirmed') {
            $data = [
                'action' => 'confirmed',
                'entries' => $entries,
            ];

            return new Response(
                $this->alerts->confirmBoxPost(
                    $this->prepareConfirmationBoxText($entries),
                    $data,
                    $this->router->route($moduleConfirmUrl),
                    $this->router->route($moduleIndexUrl)
                )
            );
        }

        return $entries;
    }

    /**
     * @return array<string|int>
     */
    private function prepareRequestData(): array
    {
        if (\is_array($this->request->getPost()->get('entries')) === true) {
            return $this->request->getPost()->get('entries');
        }

        if ((bool) preg_match('/^((\d+)\|)*(\d+)$/', $this->request->getParameters()->get('entries')) === true) {
            return explode('|', $this->request->getParameters()->get('entries'));
        }

        return [];
    }

    private function prepareConfirmationBoxText(array $entries): string
    {
        $entriesCount = \count($entries);
        if ($entriesCount === 1) {
            return $this->translator->t('system', 'confirm_delete_single');
        }

        return $this->translator->t('system', 'confirm_delete_multiple', ['{items}' => $entriesCount]);
    }
}
