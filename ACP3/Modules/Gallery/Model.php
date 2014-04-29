<?php

namespace ACP3\Modules\Gallery;

use ACP3\Core;

/**
 * Description of Model
 *
 * @author Tino Goratsch
 */
class Model extends Core\Model
{

    const TABLE_NAME = 'gallery';
    const TABLE_NAME_PICTURES = 'gallery_pictures';

    private $uri;

    public function __construct(\Doctrine\DBAL\Connection $db, Core\Lang $lang, Core\URI $uri)
    {
        parent::__construct($db, $lang);

        $this->uri;
    }

    public function galleryExists($id, $time = '')
    {

        $period = empty($time) === false ? ' AND (start = end AND start <= :time OR start != end AND :time BETWEEN start AND end)' : '';
        return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE id = :id' . $period, array('id' => $id, 'time' => $time)) > 0 ? true : false;
    }

    public function pictureExists($pictureId, $time = '')
    {
        $period = empty($time) === false ? ' AND (g.start = g.end AND g.start <= :time OR g.start != g.end AND :time BETWEEN g.start AND g.end)' : '';
        return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM ' . $this->prefix . static::TABLE_NAME . ' AS g, ' . $this->prefix . static::TABLE_NAME_PICTURES . ' AS p WHERE p.id = :id AND p.gallery_id = g.id' . $period, array('id' => $pictureId, 'time' => $time)) > 0 ? true : false;
    }

    public function getGalleryById($id)
    {
        return $this->db->fetchAssoc('SELECT * FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE id = ?', array($id));
    }

    public function getPictureById($id)
    {
        return $this->db->fetchAssoc('SELECT g.id AS gallery_id, g.title, p.* FROM ' . $this->prefix . static::TABLE_NAME . ' AS g, ' . $this->prefix . static::TABLE_NAME_PICTURES . ' AS p WHERE p.id = ? AND p.gallery_id = g.id', array($id));
    }

    public function getGalleryIdFromPictureId($pictureId)
    {
        return $this->db->fetchColumn('SELECT gallery_id FROM ' . $this->prefix . static::TABLE_NAME_PICTURES . ' WHERE id = ?', array($pictureId));
    }

    public function getLastPictureByGalleryId($galleryId)
    {
        return $this->db->fetchColumn('SELECT MAX(pic) FROM ' . $this->prefix . static::TABLE_NAME_PICTURES . ' WHERE gallery_id = ?', array($galleryId));
    }

    public function getPicturesByGalleryId($id)
    {
        return $this->db->fetchAll('SELECT * FROM ' . $this->prefix . static::TABLE_NAME_PICTURES . ' WHERE gallery_id = ? ORDER BY pic ASC', array($id));
    }

    public function getPreviousPictureId($picture, $galleryId)
    {
        return $this->db->fetchColumn('SELECT id FROM ' . $this->prefix . static::TABLE_NAME_PICTURES . ' WHERE pic < ? AND gallery_id = ? ORDER BY pic DESC LIMIT 1', array($picture, $galleryId));
    }

    public function getNextPictureId($picture, $galleryId)
    {
        return $this->db->fetchColumn('SELECT id FROM ' . $this->prefix . static::TABLE_NAME_PICTURES . ' WHERE pic > ? AND gallery_id = ? ORDER BY pic DESC LIMIT 1', array($picture, $galleryId));
    }

    public function getFileById($pictureId)
    {
        return $this->db->fetchColumn('SELECT file FROM ' . $this->prefix . static::TABLE_NAME_PICTURES . ' WHERE id = ?', array($pictureId));
    }

    public function getGalleryTitle($galleryId)
    {
        return $this->db->fetchColumn('SELECT title FROM ' . $this->prefix . static::TABLE_NAME . ' WHERE id = ?', array($galleryId));
    }

    public function countAll($time)
    {
        return count($this->getAll($time, POS));
    }

    public function getAll($time = '', $limitStart = '', $resultsPerPage = '')
    {
        $where = $time !== '' ? ' WHERE (g.start = g.end AND g.start <= :time OR g.start != g.end AND :time BETWEEN g.start AND g.end)' : '';
        $limitStmt = $this->_buildLimitStmt($limitStart, $resultsPerPage);
        return $this->db->fetchAll('SELECT g.*, COUNT(p.gallery_id) AS pics FROM ' . $this->prefix . static::TABLE_NAME . ' AS g LEFT JOIN ' . $this->prefix . static::TABLE_NAME_PICTURES . ' AS p ON(g.id = p.gallery_id) ' . $where . ' GROUP BY g.id ORDER BY g.start DESC, g.end DESC, g.id DESC' . $limitStmt, array('time' => $time));
    }

    public function getAllInAcp()
    {
        return $this->db->fetchAll('SELECT g.id, g.start, g.end, g.title, COUNT(p.gallery_id) AS pictures FROM ' . $this->prefix . static::TABLE_NAME . ' AS g LEFT JOIN ' . $this->prefix . static::TABLE_NAME_PICTURES . ' AS p ON(g.id = p.gallery_id) GROUP BY g.id ORDER BY g.start DESC, g.end DESC, g.id DESC');
    }

    public function updatePicturesNumbers($pictureNumber, $galleryId)
    {
        return $this->db->executeUpdate('UPDATE ' . $this->prefix . static::TABLE_NAME_PICTURES . ' SET pic = pic - 1 WHERE pic > ? AND gallery_id = ?', array($pictureNumber, $galleryId));
    }

    public function validateCreate(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (Core\Validate::date($formData['start'], $formData['end']) === false) {
            $errors[] = $this->lang->t('system', 'select_date');
        }
        if (strlen($formData['title']) < 3) {
            $errors['title'] = $this->lang->t('gallery', 'type_in_gallery_title');
        }
        if ((bool)CONFIG_SEO_ALIASES === true && !empty($formData['alias']) &&
            (Core\Validate::isUriSafe($formData['alias']) === false || Core\Validate::uriAliasExists($formData['alias']) === true)
        ) {
            $errors['alias'] = $this->lang->t('system', 'uri_alias_unallowed_characters_or_exists');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    public function validateCreatePicture(array $file, array $settings)
    {
        $this->validateFormKey();

        $errors = array();
        if (empty($file['tmp_name'])) {
            $errors['file'] = $this->lang->t('gallery', 'no_picture_selected');
        }
        if (!empty($file['tmp_name']) &&
            (Core\Validate::isPicture($file['tmp_name'], $settings['maxwidth'], $settings['maxheight'], $settings['filesize']) === false ||
                $_FILES['file']['error'] !== UPLOAD_ERR_OK)
        ) {
            $errors['file'] = $this->lang->t('gallery', 'invalid_image_selected');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    public function validateEdit(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (Core\Validate::date($formData['start'], $formData['end']) === false) {
            $errors[] = $this->lang->t('system', 'select_date');
        }
        if (strlen($formData['title']) < 3) {
            $errors['title'] = $this->lang->t('gallery', 'type_in_gallery_title');
        }
        if ((bool)CONFIG_SEO_ALIASES === true && !empty($formData['alias']) &&
            (Core\Validate::isUriSafe($formData['alias']) === false || Core\Validate::uriAliasExists($formData['alias'], 'gallery/pics/id_' . $this->uri->id))
        ) {
            $errors['alias'] = $this->lang->t('system', 'uri_alias_unallowed_characters_or_exists');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    public function validateEditPicture(array $file, array $settings)
    {
        $this->validateFormKey();

        $errors = array();
        if (!empty($file['tmp_name']) &&
            (Core\Validate::isPicture($file['tmp_name'], $settings['maxwidth'], $settings['maxheight'], $settings['filesize']) === false ||
                $_FILES['file']['error'] !== UPLOAD_ERR_OK)
        ) {
            $errors['file'] = $this->lang->t('gallery', 'invalid_image_selected');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }


    public function validateSettings(array $formData)
    {
        $this->validateFormKey();

        $errors = array();
        if (empty($formData['dateformat']) || ($formData['dateformat'] !== 'long' && $formData['dateformat'] !== 'short')) {
            $errors['dateformat'] = $this->lang->t('system', 'select_date_format');
        }
        if (Core\Validate::isNumber($formData['sidebar']) === false) {
            $errors['sidebar'] = $this->lang->t('system', 'select_sidebar_entries');
        }
        if (!isset($formData['overlay']) || $formData['overlay'] != 1 && $formData['overlay'] != 0) {
            $errors[] = $this->lang->t('gallery', 'select_use_overlay');
        }
        if (Core\Modules::isActive('comments') === true && (!isset($formData['comments']) || $formData['comments'] != 1 && $formData['comments'] != 0)) {
            $errors[] = $this->lang->t('gallery', 'select_allow_comments');
        }
        if (Core\Validate::isNumber($formData['thumbwidth']) === false || Core\Validate::isNumber($formData['width']) === false || Core\Validate::isNumber($formData['maxwidth']) === false) {
            $errors[] = $this->lang->t('gallery', 'invalid_image_width_entered');
        }
        if (Core\Validate::isNumber($formData['thumbheight']) === false || Core\Validate::isNumber($formData['height']) === false || Core\Validate::isNumber($formData['maxheight']) === false) {
            $errors[] = $this->lang->t('gallery', 'invalid_image_height_entered');
        }
        if (Core\Validate::isNumber($formData['filesize']) === false) {
            $errors['filesize'] = $this->lang->t('gallery', 'invalid_image_filesize_entered');
        }

        if (!empty($errors)) {
            throw new Core\Exceptions\ValidationFailed(Core\Functions::errorBox($errors));
        }
    }

    /**
     * Erstellt den Galerie-Cache anhand der angegebenen ID
     *
     * @param integer $id
     *  Die ID der zu cachenden Galerie
     * @return boolean
     */
    public function setCache($id)
    {
        $pictures = $this->getPicturesByGalleryId($id);
        $c_pictures = count($pictures);

        $settings = Core\Config::getSettings('gallery');

        for ($i = 0; $i < $c_pictures; ++$i) {
            $pictures[$i]['width'] = $settings['thumbwidth'];
            $pictures[$i]['height'] = $settings['thumbheight'];
            $picInfos = @getimagesize(UPLOADS_DIR . 'gallery/' . $pictures[$i]['file']);
            if ($picInfos !== false) {
                if ($picInfos[0] > $settings['thumbwidth'] || $picInfos[1] > $settings['thumbheight']) {
                    $newHeight = $settings['thumbheight'];
                    $newWidth = intval($picInfos[0] * $newHeight / $picInfos[1]);
                }

                $pictures[$i]['width'] = isset($newWidth) ? $newWidth : $picInfos[0];
                $pictures[$i]['height'] = isset($newHeight) ? $newHeight : $picInfos[1];
            }
        }

        return Core\Cache::create('pics_id_' . $id, $pictures, 'gallery');
    }

    /**
     * Bindet die gecachete Galerie anhand ihrer ID ein
     *
     * @param integer $id
     *  Die ID der Galerie
     * @return array
     */
    public function getCache($id)
    {
        if (Core\Cache::check('pics_id_' . $id, 'gallery') === false) {
            $this->setCache($id);
        }

        return Core\Cache::output('pics_id_' . $id, 'gallery');
    }


}
