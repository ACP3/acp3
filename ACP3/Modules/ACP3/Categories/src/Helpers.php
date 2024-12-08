<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories;

use ACP3\Core\ACL;
use ACP3\Core\Helpers\Forms;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Validation\Exceptions\InvalidFormTokenException;
use ACP3\Core\Validation\Exceptions\ValidationFailedException;
use ACP3\Core\Validation\Exceptions\ValidationRuleNotFoundException;
use ACP3\Modules\ACP3\Categories\Repository\CategoryRepository;
use ACP3\Modules\ACP3\Categories\Services\CategoryUpsertService;
use Doctrine\DBAL\Exception;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;

class Helpers
{
    public function __construct(private readonly ACL $acl, private readonly Translator $translator, private readonly RequestInterface $request, private readonly Forms $formsHelper, private readonly CategoryUpsertService $categoryUpsertService, private readonly CategoryRepository $categoryRepository)
    {
    }

    /**
     * Erzeugt eine neue Kategorie und gibt ihre ID zurück.
     *
     * @deprecated since ACP3 version 6.6.0, to be removed with version 7.0.0. Use `CategoryUpsertService::createCategoryInline` instead.
     *
     * @throws InvalidFormTokenException
     * @throws ValidationFailedException
     * @throws ValidationRuleNotFoundException
     * @throws Exception
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    public function categoriesCreate(string $categoryTitle, string $moduleName): int
    {
        return $this->categoryUpsertService->createCategoryInline($categoryTitle, $moduleName);
    }

    /**
     * Listet alle Kategorien eines Moduls auf.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function categoriesList(
        string $moduleName,
        ?int $categoryId = null,
        bool $categoryCreate = false,
        string $formFieldName = 'cat',
        ?string $customText = null,
    ): array {
        $categories = $this->categoryRepository->getAllByModuleName($moduleName);
        foreach ($categories as &$category) {
            $category['title'] = str_repeat('&nbsp;&nbsp;', $category['level']) . $category['title'];
            $category['selected'] = $this->formsHelper->selectEntry(
                $formFieldName,
                $category['id'],
                $categoryId
            );
        }

        return [
            'custom_text' => $customText ?: $this->translator->t('system', 'pls_select'),
            'name' => $formFieldName,
            'categories' => $categories,
            'create' => $this->addCreateCategoryFormFields($categoryCreate, $formFieldName),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function addCreateCategoryFormFields(bool $categoryCreate, string $formFieldName): array
    {
        $formFields = [];
        if ($categoryCreate === true && $this->acl->hasPermission('admin/categories/index/create') === true) {
            $formFields['name'] = $formFieldName . '_create';
            $formFields['value'] = $this->request->getPost()->all('create')['name'] ?? '';
        }

        return $formFields;
    }
}
