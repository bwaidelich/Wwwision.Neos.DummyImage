<?php
namespace Wwwision\Neos\DummyImage\Unit;

use Neos\Flow\Tests\UnitTestCase;
use Neos\Media\Domain\Model\Adjustment\ResizeImageAdjustment;
use Neos\Media\Domain\Model\ImageInterface;
use Neos\Media\Domain\Model\ThumbnailConfiguration;
use Neos\Media\Imagine\Box;
use Wwwision\Neos\DummyImage\DummyImage;

class DummyImageTest extends UnitTestCase
{

    public function resizeImageAdjustmentDataProvider()
    {
        return [
            'widthAndHeightDeterminedByExplicitlySetWidthAndHeightWithInsetMode' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 110, 'height' => 110, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => null, 'allowUpScaling' => null, 'expectedWidth' => 110, 'expectedHeight' => 83, 'expectedCropRatio' => 0],
            'widthAndHeightDeterminedByExplicitlySetWidthAndHeightWithOutboundMode' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 110, 'height' => 110, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => true, 'allowUpScaling' => null, 'expectedWidth' => 110, 'expectedHeight' => 110, 'expectedCropRatio' => 36.66],

            'ifWidthIsSetHeightIsDeterminedByTheOriginalAspectRatio' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 110, 'height' => null, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => false, 'allowUpScaling' => null, 'expectedWidth' => 110, 'expectedHeight' => 83, 'expectedCropRatio' => 0],
            'ifHeightIsSetWidthIsDeterminedByTheOriginalAspectRatio' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => 95, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => false, 'allowUpScaling' => null, 'expectedWidth' => 127, 'expectedHeight' => 95, 'expectedCropRatio' => 0],

            'minimumHeightIsGreaterZero' => ['originalWidth' => 2000, 'originalHeight' => 2, 'width' => null, 'height' => null, 'maximumWidth' => 250, 'maximumHeight' => 250, 'allowCropping' => false, 'allowUpScaling' => null, 'expectedWidth' => 250, 'expectedHeight' => 1, 'expectedCropRatio' => 0],
            'minimumWidthIsGreaterZero' => ['originalWidth' => 2, 'originalHeight' => 2000, 'width' => null, 'height' => null, 'maximumWidth' => 250, 'maximumHeight' => 250, 'allowCropping' => false, 'allowUpScaling' => null, 'expectedWidth' => 1, 'expectedHeight' => 250, 'expectedCropRatio' => 0],

            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_0' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => null, 'maximumWidth' => 110, 'maximumHeight' => null, 'allowCropping' => false, 'allowUpScaling' => false, 'expectedWidth' => 110, 'expectedHeight' => 83, 'expectedCropRatio' => 0],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_1' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => null, 'maximumWidth' => 110, 'maximumHeight' => 80, 'allowCropping' => false, 'allowUpScaling' => false, 'expectedWidth' => 106, 'expectedHeight' => 80, 'expectedCropRatio' => 0],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_2' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => null, 'maximumWidth' => 110, 'maximumHeight' => 80, 'allowCropping' => false, 'allowUpScaling' => true, 'expectedWidth' => 106, 'expectedHeight' => 80, 'expectedCropRatio' => 0],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_3' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => null, 'maximumWidth' => 110, 'maximumHeight' => null, 'allowCropping' => true, 'allowUpScaling' => false, 'expectedWidth' => 110, 'expectedHeight' => 83, 'expectedCropRatio' => .66],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_4' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => null, 'maximumWidth' => 110, 'maximumHeight' => 80, 'allowCropping' => true, 'allowUpScaling' => false, 'expectedWidth' => 106, 'expectedHeight' => 80, 'expectedCropRatio' => .66],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_5' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => null, 'height' => null, 'maximumWidth' => 110, 'maximumHeight' => 80, 'allowCropping' => true, 'allowUpScaling' => true, 'expectedWidth' => 106, 'expectedHeight' => 80, 'expectedCropRatio' => .66],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_6' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 500, 'height' => null, 'maximumWidth' => null, 'maximumHeight' => 310, 'allowCropping' => false, 'allowUpScaling' => false, 'expectedWidth' => 400, 'expectedHeight' => 300, 'expectedCropRatio' => 0],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_7' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 500, 'height' => null, 'maximumWidth' => null, 'maximumHeight' => 310, 'allowCropping' => false, 'allowUpScaling' => true, 'expectedWidth' => 413, 'expectedHeight' => 310, 'expectedCropRatio' => 0],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_8' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 500, 'height' => 500, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => true, 'allowUpScaling' => false, 'expectedWidth' => 300, 'expectedHeight' => 300, 'expectedCropRatio' => 100],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_9' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 500, 'height' => 500, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => true, 'allowUpScaling' => true, 'expectedWidth' => 500, 'expectedHeight' => 500, 'expectedCropRatio' => 166.66],
            'combinationsOfMaximumAndMinimumWidthAndHeightAreCalculatedCorrectly_10' => ['originalWidth' => 400, 'originalHeight' => 300, 'width' => 500, 'height' => 500, 'maximumWidth' => 450, 'maximumHeight' => 445, 'allowCropping' => true, 'allowUpScaling' => true, 'expectedWidth' => 445, 'expectedHeight' => 445, 'expectedCropRatio' => 148.33],

            'moreTests_0' => ['originalWidth' => 1300, 'originalHeight' => 3000, 'width' => 4000, 'height' => 1100, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => false, 'allowUpScaling' => true, 'expectedWidth' => 477, 'expectedHeight' => 1100, 'expectedCropRatio' => 0],
            'moreTests_1' => ['originalWidth' => 130, 'originalHeight' => 300, 'width' => 400, 'height' => 110, 'maximumWidth' => null, 'maximumHeight' => null, 'allowCropping' => true, 'allowUpScaling' => true, 'expectedWidth' => 400, 'expectedHeight' => 110, 'expectedCropRatio' => -813],
        ];
    }

    /**
     * @test
     * @dataProvider resizeImageAdjustmentDataProvider
     */
    public function resizeImageAdjustmentTests(int $originalWidth = null, int $originalHeight = null, int $width = null, $height = null, int $maximumWidth = null, int $maximumHeight = null, bool $allowCropping = null, bool $allowUpScaling = null, int $expectedWidth = null, int $expectedHeight = null)
    {
        /** @var ResizeImageAdjustment $adjustment */
        $adjustment = $this->getAccessibleMock(ResizeImageAdjustment::class, array('dummy'));

        $originalDimensions = new Box($originalWidth, $originalHeight);
        $expectedDimensions = new Box($expectedWidth, $expectedHeight);

        if ($width !== null) {
            $adjustment->setWidth($width);
        }
        if ($height !== null) {
            $adjustment->setHeight($height);
        }
        if ($maximumWidth !== null) {
            $adjustment->setMaximumWidth($maximumWidth);
        }
        if ($maximumHeight !== null) {
            $adjustment->setMaximumHeight($maximumHeight);
        }
        if ($allowCropping !== null) {
            $adjustment->setRatioMode($allowCropping ? ImageInterface::RATIOMODE_OUTBOUND : ImageInterface::RATIOMODE_INSET);
        }
        if ($allowUpScaling !== null) {
            $adjustment->setAllowUpScaling($allowUpScaling);
        }

        $this->assertEquals($expectedDimensions, $adjustment->_call('calculateDimensions', $originalDimensions));
    }

    /**
     * @test
     * @dataProvider resizeImageAdjustmentDataProvider
     */
    public function dummyImageTests(int $originalWidth = null, int $originalHeight = null, int $width = null, $height = null, int $maximumWidth = null, int $maximumHeight = null, bool $allowCropping = null, bool $allowUpScaling = null, int $expectedWidth = null, int $expectedHeight = null)
    {
        $dummyImage = DummyImage::withSize($originalWidth, $originalHeight);
        $thumbnailConfiguration = new ThumbnailConfiguration($width, $maximumWidth, $height, $maximumHeight, $allowCropping, $allowUpScaling);
        $resultingImage = $dummyImage->resize($thumbnailConfiguration);

        $expectedDimensions = [$expectedWidth, $expectedHeight];
        $actualDimensions = [$resultingImage->getWidth(), $resultingImage->getHeight()];
        $this->assertEquals($expectedDimensions, $actualDimensions);
    }

    /**
     * @test
     * @dataProvider resizeImageAdjustmentDataProvider
     */
    public function dummyImageCropRatioTests(int $originalWidth = null, int $originalHeight = null, int $width = null, $height = null, int $maximumWidth = null, int $maximumHeight = null, bool $allowCropping = null, bool $allowUpScaling = null, int $expectedWidth = null, int $expectedHeight = null, float $expectedCropRatio = 0)
    {
        $dummyImage = DummyImage::withSize($originalWidth, $originalHeight);
        $thumbnailConfiguration = new ThumbnailConfiguration($width, $maximumWidth, $height, $maximumHeight, $allowCropping, $allowUpScaling);
        $resultingImage = $dummyImage->resize($thumbnailConfiguration);

        $this->assertEquals($expectedCropRatio, $resultingImage->getCropRatio(), '', 1);
    }
}
