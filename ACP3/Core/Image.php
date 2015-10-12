<?php
namespace ACP3\Core;

use Symfony\Component\HttpFoundation\Response;

/**
 * Klasse zum beliebigen Skalieren und Ausgeben von Bildern
 * @package ACP3\Core
 */
class Image
{
    /**
     * @var boolean
     */
    protected $enableCache = false;
    /**
     * @var string
     */
    protected $cacheDir = 'images/';
    /**
     * @var string
     */
    protected $cachePrefix = '';
    /**
     * @var integer
     */
    protected $maxWidth = 0;
    /**
     * @var integer
     */
    protected $maxHeight = 0;
    /**
     * @var integer
     */
    protected $jpgQuality = 85;
    /**
     * @var boolean
     */
    protected $preferWidth = false;
    /**
     * @var boolean
     */
    protected $preferHeight = false;
    /**
     * @var string
     */
    protected $file = '';
    /**
     * @var boolean
     */
    protected $forceResample = false;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;
    /**
     * @var resource
     */
    protected $image;

    /**
     * Konstruktor der Klasse.
     * Überschreibt die Defaultwerte mit denen im $options-array enthaltenen Werten
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param array                                      $options
     */
    public function __construct(
        Response $response,
        array $options
    )
    {
        $this->response = $response;

        if (isset($options['enable_cache'])) {
            $this->enableCache = (bool)$options['enable_cache'];
        }
        if (isset($options['cache_prefix'])) {
            $this->cachePrefix = $options['cache_prefix'];
        }
        if ($this->cachePrefix !== '' && !preg_match('/_$/', $this->cachePrefix)) {
            $this->cachePrefix .= '_';
        }
        if (isset($options['max_width'])) {
            $this->maxWidth = (int)$options['max_width'];
        }
        if (isset($options['max_height'])) {
            $this->maxHeight = (int)$options['max_height'];
        }
        if (isset($options['prefer_width'])) {
            $this->preferWidth = (bool)$options['prefer_width'];
        }
        if (isset($options['prefer_height'])) {
            $this->preferHeight = (bool)$options['prefer_height'];
        }
        if (isset($options['jpg_quality'])) {
            $this->jpgQuality = (int)$options['jpg_quality'];
        }
        if (isset($options['force_resample'])) {
            $this->forceResample = (bool)$options['force_resample'];
        }

        $this->file = $options['file'];
    }

    /**
     * Gibt den während der Bearbeitung belegten Speicher wieder frei
     */
    public function __destruct()
    {
        if (is_resource($this->image) === true) {
            imagedestroy($this->image);
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function output()
    {
        if (is_file($this->file) === true) {
            $cacheFile = $this->getCacheFileName();
            $picInfo = getimagesize($this->file);
            $width = $picInfo[0];
            $height = $picInfo[1];
            $type = $picInfo[2];

            $this->setHeaders($picInfo['mime']);

            // Falls Cache aktiviert ist und das Bild bereits gecachet wurde, dieses direkt ausgeben
            if ($this->enableCache === true && is_file($cacheFile) === true) {
                $this->file = $cacheFile;
            } elseif ($this->resamplingIsNecessary($width, $height, $type)) { // Bild resampeln
                $dimensions = $this->calcNewDimensions($width, $height);

                $this->createCacheDir();

                $this->resample(
                    $dimensions['width'],
                    $dimensions['height'],
                    $width,
                    $height,
                    $type,
                    $cacheFile
                );
                $this->file = $cacheFile;
            }

            $this->response->setContent($this->readFromFile());
        } else {
            $this->setHeaders('image/jpeg');
        }

        return $this->response;
    }

    /**
     * Get the name of a possibly cached picture
     *
     * @return string
     */
    protected function getCacheFileName()
    {
        return CACHE_DIR . $this->cacheDir . $this->getCacheName();
    }

    /**
     * Generiert den Namen des zu cachenden Bildes
     *
     * @return string
     */
    protected function getCacheName()
    {
        return $this->cachePrefix . substr($this->file, strrpos($this->file, '/') + 1);
    }

    /**
     * Gibt ein Bild direkt aus, ohne dieses in der Größe zu bearbeiten
     *
     * @return integer
     */
    protected function readFromFile()
    {
        return readfile($this->file);
    }

    /**
     * Berechnet die neue Breite/Höhe eines Bildes
     *
     * @param integer $width
     *  Ausgangsbreite des Bildes
     * @param integer $height
     *  Ausgangshöhe des Bildes
     *
     * @return array
     */
    protected function calcNewDimensions($width, $height)
    {
        if (($width >= $height || $this->preferWidth === true) && $this->preferHeight === false) {
            $newWidth = $this->maxWidth;
            $newHeight = intval($height * $newWidth / $width);
        } else {
            $newHeight = $this->maxHeight;
            $newWidth = intval($width * $newHeight / $height);
        }

        return ['width' => $newWidth, 'height' => $newHeight];
    }

    /**
     * Führt die Größenanpassung des Bildes durch
     *
     * @param integer $newWidth
     * @param integer $newHeight
     * @param integer $width
     * @param integer $height
     * @param integer $type
     * @param string  $cacheFile
     */
    protected function resample($newWidth, $newHeight, $width, $height, $type, $cacheFile = null)
    {
        $this->image = imagecreatetruecolor($newWidth, $newHeight);
        switch ($type) {
            case 1:
                $origPicture = imagecreatefromgif($this->file);
                $this->scalePicture($newWidth, $newHeight, $width, $height, $origPicture);
                imagegif($this->image, $cacheFile);
                break;
            case 2:
                $origPicture = imagecreatefromjpeg($this->file);
                $this->scalePicture($newWidth, $newHeight, $width, $height, $origPicture);
                imagejpeg($this->image, $cacheFile, $this->jpgQuality);
                break;
            case 3:
                imagealphablending($this->image, false);
                $origPicture = imagecreatefrompng($this->file);
                $this->scalePicture($newWidth, $newHeight, $width, $height, $origPicture);
                imagesavealpha($this->image, true);
                imagepng($this->image, $cacheFile, 9);
                break;
        }
    }

    /**
     * @param string $mimeType
     */
    protected function setHeaders($mimeType)
    {
        $this->response->headers->add([
            'Content-type' => $mimeType,
            'Cache-Control' => 'public',
            'Pragma' => 'public',
            'Last-Modified' => gmdate('D, d M Y H:i:s', filemtime($this->file)) . ' GMT',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT'
        ]);
    }

    /**
     * @param int $newWidth
     * @param int $newHeight
     * @param int $width
     * @param int $height
     * @param resource $origPicture
     */
    protected function scalePicture($newWidth, $newHeight, $width, $height, $origPicture)
    {
        imagecopyresampled($this->image, $origPicture, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $type
     *
     * @return bool
     */
    protected function resamplingIsNecessary($width, $height, $type)
    {
        return ($this->forceResample === true || ($width > $this->maxWidth || $height > $this->maxHeight)) && ($type === 1 || $type === 2 || $type === 3);
    }

    /**
     * Creates the cache directory if it's not already present
     */
    protected function createCacheDir()
    {
        if (!is_dir($this->cacheDir) && is_writable($this->cacheDir)) {
            mkdir(CACHE_DIR . $this->cacheDir);
        }
    }
}
