<?php
namespace Wwwision\Neos\DummyImage\FusionObjects;

use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Wwwision\Neos\DummyImage\DummyImage;

/**
 * A Fusion object representing a DummyImage
 *
 * Usage:
 *
 * image = Neos.Neos:ImageTag {
 *     asset = Wwwision.Neos.DummyImage:DummyImage {
 *         width = 600
 *         height = 400
 *     }
 * }
 */
class DummyImageImplementation extends AbstractFusionObject
{

    public function evaluate(): DummyImage
    {
        $imageWidth = (int)$this->fusionValue('width');
        $imageHeight = (int)$this->fusionValue('height');
        return DummyImage::withSize($imageWidth, $imageHeight);
    }
}
