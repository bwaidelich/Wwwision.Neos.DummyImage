<?php
namespace Wwwision\Neos\DummyImage\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Wwwision\Neos\DummyImage\DummyImage;

/**
 * An AOP aspect intercepting calls to AssetService::getThumbnailUriAndSizeForAsset() in order to return custom results for DummyImage instances
 *
 * @Flow\Aspect
 */
class AssetServiceAspect
{

    /**
     * @Flow\Around("method(Neos\Media\Domain\Service\AssetService->getThumbnailUriAndSizeForAsset())")
     */
    public function getThumbnailUriAndSizeForAssetAspect(JoinPointInterface $joinPoint): array
    {
        /** @var AssetInterface $asset */
        $asset = $joinPoint->getMethodArgument('asset');
        if (!$asset instanceof DummyImage) {
            return $joinPoint->getAdviceChain()->proceed($joinPoint);
        }

        /** @var ThumbnailConfiguration $thumbnailConfiguration */
        $thumbnailConfiguration = $joinPoint->getMethodArgument('configuration');
        $thumbnailAsset = $asset->resize($thumbnailConfiguration);

        return [
            'width' => $thumbnailAsset->getWidth(),
            'height' => $thumbnailAsset->getHeight(),
            'src' => $thumbnailAsset->getDataUrl(),
        ];
    }
}
