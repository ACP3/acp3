<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Picture;

use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Picture\Exception\PictureResponseException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class Output
{
    private ?int $srcWidth = null;

    private ?int $srcHeight = null;

    private ?string $destFile = null;

    private ?int $destWidth = null;

    private ?int $destHeight = null;

    public function __construct(private readonly ApplicationPath $appPath, private readonly string $srcFile, private readonly int $type)
    {
    }

    public function getSrcFile(): string
    {
        return $this->srcFile;
    }

    public function getSrcWidth(): ?int
    {
        return $this->srcWidth;
    }

    public function setSrcWidth(?int $srcWidth): self
    {
        $this->srcWidth = $srcWidth;

        return $this;
    }

    public function getSrcHeight(): ?int
    {
        return $this->srcHeight;
    }

    public function setSrcHeight(?int $srcHeight): self
    {
        $this->srcHeight = $srcHeight;

        return $this;
    }

    public function getDestFile(): ?string
    {
        return $this->destFile;
    }

    public function setDestFile(string $destFile): self
    {
        $this->destFile = $destFile;

        return $this;
    }

    public function getDestWidth(): ?int
    {
        return $this->destWidth;
    }

    public function setDestWidth(?int $destWidth): self
    {
        $this->destWidth = $destWidth;

        return $this;
    }

    public function getDestHeight(): ?int
    {
        return $this->destHeight;
    }

    public function setDestHeight(?int $destHeight): self
    {
        $this->destHeight = $destHeight;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getFileWeb(): string
    {
        return $this->appPath->getWebRoot() . substr(str_replace(ACP3_ROOT_DIR, '', $this->getFile()), 1);
    }

    public function getFile(): string
    {
        return $this->getDestFile() ?? $this->getSrcFile();
    }

    public function getWidth(): int
    {
        return $this->getDestWidth() ?? $this->getSrcWidth();
    }

    public function getHeight(): int
    {
        return $this->getDestHeight() ?? $this->getSrcHeight();
    }

    /**
     * @deprecated since ACP3 version 6.7.0, to be removed with 7.0.0.
     *
     * @throws PictureResponseException
     */
    public function sendResponse(): BinaryFileResponse
    {
        $response = new BinaryFileResponse($this->getFile());
        $this->setHeaders($response, $this->getMimeType($this->getType()));

        return $response;
    }

    /**
     * @deprecated since ACP3 version 6.7.0, to be removed with 7.0.0.
     *
     * @throws PictureResponseException
     */
    private function getMimeType(int $pictureType): string
    {
        return match ($pictureType) {
            IMAGETYPE_GIF => 'image/gif',
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_WEBP => 'image/webp',
            default => throw new PictureResponseException(\sprintf('Unsupported picture type: %s', $pictureType)),
        };
    }

    /**
     * @deprecated since ACP3 version 6.7.0, to be removed with 7.0.0.
     */
    private function setHeaders(BinaryFileResponse $response, string $mimeType): void
    {
        $response->headers->add([
            'Content-type' => $mimeType,
            'Cache-Control' => 'public',
            'Pragma' => 'public',
            'Last-Modified' => gmdate('D, d M Y H:i:s', (int) filemtime($this->getFile())) . ' GMT',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31_536_000) . ' GMT',
        ]);
    }
}
