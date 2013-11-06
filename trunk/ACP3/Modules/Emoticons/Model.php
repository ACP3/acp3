<?php

namespace ACP3\Modules\Emoticons;

use ACP3\Core;

/**
 * Description of Model
 *
 * @author goratsch
 */
class Model extends Core\Model
{

    const TABLE_NAME = 'emoticons';

    public function __construct(\Doctrine\DBAL\Connection $db)
    {
        parent::__construct($db);
    }

    public function resultExists($id)
    {
        return $this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE id = ?', array($id)) > 0 ? true : false;
    }

    public function resultsExist($moduleId)
    {
        return $this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE module_id = ?', array($moduleId));
    }

    public function getOneById($id)
    {
        return $this->db->fetchAssoc('SELECT * FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE id = ?', array($id));
    }

    public function getOneImageById($id)
    {
        return $this->db->fetchColumn('SELECT img FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE id = ?', array($id));
    }

    public function getAll()
    {
        return $this->db->fetchAll('SELECT * FROM ' . $this->prefix . static::TABLE_NAME . ' ORDER BY id DESC');
    }

    public function validateCreate(array $formData, $file, \ACP3\Core\Lang $lang)
    {
        if (Core\Validate::formToken() === false) {
            throw new Core\Exceptions\InvalidFormToken($lang->t('system', 'form_already_submitted'));
        }

        $settings = Core\Config::getSettings('emoticons');

        if (empty($formData['code'])) {
            $errors['code'] = $lang->t('emoticons', 'type_in_code');
        }
        if (empty($formData['description'])) {
            $errors['description'] = $lang->t('emoticons', 'type_in_description');
        }
        if (Core\Validate::isPicture($file['tmp_name'], $settings['width'], $settings['height'], $settings['filesize']) === false ||
            $_FILES['picture']['error'] !== UPLOAD_ERR_OK
        ) {
            $errors['picture'] = $lang->t('emoticons', 'invalid_image_selected');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    public function validateEdit(array $formData, $file, \ACP3\Core\Lang $lang)
    {
        if (Core\Validate::formToken() === false) {
            throw new Core\Exceptions\InvalidFormToken($lang->t('system', 'form_already_submitted'));
        }

        $settings = Core\Config::getSettings('emoticons');

        if (empty($formData['code'])) {
            $errors['code'] = $lang->t('emoticons', 'type_in_code');
        }
        if (empty($formData['description'])) {
            $errors['description'] = $lang->t('emoticons', 'type_in_description');
        }
        if (!empty($file) && (Core\Validate::isPicture($file['tmp_name'], $settings['width'], $settings['height'], $settings['filesize']) === false || $_FILES['picture']['error'] !== UPLOAD_ERR_OK)) {
            $errors['picture'] = $lang->t('emoticons', 'invalid_image_selected');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    public function validateSettings(array $formData, \ACP3\Core\Lang $lang)
    {
        if (Core\Validate::formToken() === false) {
            throw new Core\Exceptions\InvalidFormToken($lang->t('system', 'form_already_submitted'));
        }

        $errors = array();
        if (Core\Validate::isNumber($formData['width']) === false) {
            $errors['width'] = $lang->t('emoticons', 'invalid_image_width_entered');
        }
        if (Core\Validate::isNumber($formData['height']) === false) {
            $errors['height'] = $lang->t('emoticons', 'invalid_image_height_entered');
        }
        if (Core\Validate::isNumber($formData['filesize']) === false) {
            $errors['filesize'] = $lang->t('emoticons', 'invalid_image_filesize_entered');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

}
