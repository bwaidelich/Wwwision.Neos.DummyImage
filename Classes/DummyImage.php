<?php
namespace Wwwision\Neos\DummyImage;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\AssetSource\AssetProxy\AssetProxyInterface;
use Neos\Media\Domain\Model\DimensionsTrait;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\Thumbnail;
use Neos\Media\Domain\Model\ThumbnailConfiguration;

/**
 * The DummyImage implementation
 *
 * @Flow\Proxy(false)
 */
final class DummyImage implements AssetInterface, ImageInterface
{

    use DimensionsTrait;

    /**
     * @var string
     */
    private $source;

    /**
     * @var float
     */
    private $cropRatio;

    /**
     * @var ?int
     */
    private $originalWidth;

    /**
     * @var ?int
     */
    private $originalHeight;

    /**
     * @var bool
     */
    private $cropped;

    private function __construct(int $width, int $height, int $originalWidth = null, int $originalHeight = null, bool $cropped = false)
    {
        $this->width = $width;
        $this->height = $height;
        $this->originalWidth = $originalWidth;
        $this->originalHeight = $originalHeight;
        $this->cropped = $cropped;
        $this->cropRatio = $this->calculateCropRatio();
    }

    public static function withSize(int $width, int $height): self
    {
        return new static($width, $height);
    }

    public function resize(ThumbnailConfiguration $thumbnailConfiguration): self
    {
        // Congratulations for digging this deep into the code!
        // You made it to the part that I am the least proud of: The resizing logic.
        // So viewer discretion advised.
        // But, hey, I merely copied the convoluted logic from Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment,
        // copied their tests and added some additional ones.
        // So at least I could always refactor this – if I ever feel like ;)

        $newWidth = $this->width;
        $newHeight = $this->height;
        $isCropped = false;
        if ($thumbnailConfiguration->getWidth() !== null && $thumbnailConfiguration->getHeight() !== null) {
            if ($thumbnailConfiguration->isCroppingAllowed()) {
                $newWidth = $thumbnailConfiguration->getWidth();
                $newHeight = $thumbnailConfiguration->getHeight();
                $isCropped = ($newWidth / $newHeight) !== ($this->width / $this->height);
                #if (!$thumbnailConfiguration->isUpScalingAllowed() && !($this->width >= $thumbnailConfiguration->getWidth() || $this->height >= $thumbnailConfiguration->getHeight())) {
                if ($thumbnailConfiguration->isUpScalingAllowed() !== true && ($this->width < $thumbnailConfiguration->getWidth() || $this->height < $thumbnailConfiguration->getHeight())) {
                    $ratio = min(
                        $this->width / $thumbnailConfiguration->getWidth(),
                        $this->height / $thumbnailConfiguration->getHeight()
                    );
                    $newWidth = (int)round($ratio * $newWidth);
                    $newHeight = (int)round($ratio * $newHeight);
                }
            } else {
                $ratio = min(
                    $thumbnailConfiguration->getWidth() / $this->width,
                    $thumbnailConfiguration->getHeight() / $this->height
                );
                $newWidth = (int)round($ratio * $this->width);
                $newHeight = (int)round($ratio * $this->height);
            }
        } elseif ($thumbnailConfiguration->getWidth() !== null) {
            if ($thumbnailConfiguration->isUpScalingAllowed() || $thumbnailConfiguration->getWidth() < $this->width) {
                $ratio = $thumbnailConfiguration->getWidth() / $this->width;
                $newWidth = (int)round($ratio * $this->width);
                $newHeight = (int)round($ratio * $this->height);
            }
        } elseif ($thumbnailConfiguration->getHeight() !== null) {
            if ($thumbnailConfiguration->isUpScalingAllowed() || $thumbnailConfiguration->getHeight() < $this->height) {
                $ratio = $thumbnailConfiguration->getHeight() / $this->height;
                $newWidth = (int)round($ratio * $this->width);
                $newHeight = (int)round($ratio * $this->height);
            }
        }

        if ($thumbnailConfiguration->getMaximumWidth() !== null && $newWidth > $thumbnailConfiguration->getMaximumWidth()) {
            $ratio = $thumbnailConfiguration->getMaximumWidth() / $newWidth;
            $newWidth = (int)max(round($ratio * $newWidth), 1);
            $newHeight = (int)max(round($ratio * $newHeight), 1);
        }
        if ($thumbnailConfiguration->getMaximumHeight() !== null && $newHeight > $thumbnailConfiguration->getMaximumHeight()) {
            $ratio = $thumbnailConfiguration->getMaximumHeight() / $newHeight;
            $newWidth = (int)max(round($ratio * $newWidth), 1);
            $newHeight = (int)max(round($ratio * $newHeight), 1);
        }

        if ($newWidth !== $this->width || $newHeight !== $this->height) {
            return new static($newWidth, $newHeight, $this->width, $this->height, $isCropped);
        }
        return $this;
    }

    public function getTitle(): string
    {
        return (string)$this;
    }

    public function setTitle($title): void
    {
        throw new \RuntimeException('setTitle() not implemented');
    }

    public function setResource(PersistentResource $resource): void
    {
        throw new \RuntimeException('setResource() not implemented');
    }

    public function getResource(): PersistentResource
    {
        throw new \RuntimeException('getResource() not implemented');
    }

    public function getMediaType(): string
    {
        return 'image/svg+xml';
    }

    public function getFileExtension(): string
    {
        return 'svg';
    }

    public function getFileSize(): int
    {
        return strlen($this->render());
    }

    public function refresh(): void
    {
        // not used
    }

    public function setAssetSourceIdentifier(string $assetSourceIdentifier): void
    {
        // not used
    }

    public function getAssetSourceIdentifier(): ?string
    {
        // not used
    }

    public function getAssetProxy(): ?AssetProxyInterface {
        // not used
    }

    public function getThumbnail($maximumWidth = null, $maximumHeight = null, $ratioMode = ImageInterface::RATIOMODE_INSET, $allowUpScaling = null): Thumbnail
    {
        throw new \RuntimeException('getThumbnail() not implemented');
    }

    public function addThumbnail(Thumbnail $thumbnail): void
    {
        throw new \RuntimeException('addThumbnail() not implemented');
    }

    public function getDataUrl(): string
    {
        return 'data:image/svg+xml;utf8,' . rawurlencode(trim($this->render()));
    }

    public function isResized(): bool
    {
        return $this->originalWidth !== null;
    }

    public function getOriginalWidth(): ?int
    {
        return $this->originalWidth;
    }

    public function getOriginalHeight(): ?int
    {
        return $this->originalHeight;
    }

    public function isCropped(): bool
    {
        return $this->cropped;
    }

    public function getCropRatio(): float
    {
        return $this->cropRatio;
    }

    private function calculateCropRatio(): float
    {
        if ($this->cropped && $this->width > 1 && $this->height > 1) {
            $ratioOriginal = $this->originalWidth / $this->originalHeight;
            $ratioThis = $this->width / $this->height;
            if ($ratioOriginal > $ratioThis) {
                return -($this->width - ($ratioOriginal * $this->height));
            } elseif ($ratioOriginal < $ratioThis) {
                return $this->height - ($this->width / $ratioOriginal);
            }
        }
        return 0;
    }

    private function render(): string
    {
        if ($this->source !== null) {
            return $this->source;
        }

        $strokeX1 = 0;
        $strokeY1 = 0;
        $strokeX2 = $this->width;
        $strokeY2 = $this->height;
        $strokeColor = '#b5b5b5';
        $backgroundColor = '#ccc';

        if ($this->cropped) {
            if ($this->cropRatio > 0) {
                $strokeX1 -= round($this->cropRatio / 2);
                $strokeX2 += round($this->cropRatio / 2);
            } else {
                $strokeY1 -= round(-$this->cropRatio / 2);
                $strokeY2 += round(-$this->cropRatio / 2);
            }
            $strokeColor = '#996B6B';
        }

        $boxX1 = 1;
        $boxY1 = 1;
        $boxWidth = $this->width - 2;
        $boxHeight = $this->height - 2;

        $text1Size = 100;
        $text2Size = 15;
        $text1Color = '#f5f5f5';
        $text2Color = '#888';
        $deltaY = 40;
        if ($this->width < 40 || $this->height < 10) {
          $text1Size = 6;
          $text2Size = 0;
        } elseif ($this->width < 80 || $this->height < 20) {
          $text1Size = 10;
          $text2Size = 0;
        } elseif ($this->width < 140 || $this->height < 50) {
          $text1Size = 20;
          $text2Size = 0;
        } elseif ($this->width < 250 || $this->height < 100) {
          $text1Size = 30;
          $text2Size = 8;
          $deltaY = 25;
        } elseif ($this->width < 500 || $this->height < 150) {
          $text1Size = 50;
          $text2Size = 10;
          $deltaY = 35;
        }

        $resizeNote = '';
        if ($this->isResized() && $text2Size > 0) {
            $resizeNote = "Original: {$this->originalWidth} x {$this->originalHeight}";
            if ($this->isCropped()) {
                $resizeNote .= ', cropped';
            }
            $resizeNote = "<tspan x=\"50%\" dy=\"$deltaY\" font-size=\"$text2Size\" fill=\"$text2Color\">$resizeNote</tspan>";
        }

        $this->source = <<<EOT
            <svg xmlns="http://www.w3.org/2000/svg" width="{$this->width}" height="{$this->height}" font-size="100%">
                <rect x="{$boxX1}" y="{$boxY1}" width="{$boxWidth}" height="{$boxHeight}" style="fill:{$backgroundColor};stroke:{$strokeColor};stroke-width:1" vector-effect="non-scaling-stroke"/>
                <g stroke-width="1" stroke="{$strokeColor}">
                    <line x1="{$strokeX1}" y1="{$strokeY1}" x2="{$strokeX2}" y2="{$strokeY2}" vector-effect="non-scaling-stroke"/>
                    <line x1="{$strokeX2}" y1="{$strokeY1}" x2="{$strokeX1}" y2="{$strokeY2}" vector-effect="non-scaling-stroke"/>
                </g>
                <text y="50%" x="50%" font-size="{$text1Size}" text-anchor="middle" alignment-baseline="central" font-family="system-ui" fill="{$text1Color}">
                    <tspan dy="0.2em">{$this->width} x {$this->height}</tspan>
                    {$resizeNote}
                </text>
            </svg>
EOT;
        return $this->source;
    }

    public function __toString()
    {
        $string = sprintf('Dummy image (%dx%d)', $this->width, $this->height);
        if ($this->isResized()) {
            $string .= sprintf(', resized from %dx%d', $this->originalWidth, $this->originalHeight);
        }
        if ($this->isCropped()) {
            $string .= ', cropped';
        }
        return $string;
    }

}
