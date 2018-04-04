# Wwwision.Neos.DummyImage

Package that allows for rendering dynamic dummy images in the [Neos](https://www.neos.io) backend.

## Background

Do you use one of the many dummy image providers like dummyimage.com or lorempixel.com to render
placeholder images in your Neos backend?

If so this package might be useful to you:
It provides a custom `DummyImage` class implementing the Image and Asset interfaces of the `Neos.Media` package
allowing to use it as a replacement to render dynamic placeholder images that can be resized and cropped.

This is particularity useful when used in conjunction with Fusion.

## Usage

You can easily install this package via [composer](https://getcomposer.org):

```
composer require wwwision/neos-dummyimage
```

### Example: Basic Fusion implementation

```
someImage = Neos.Neos:ImageTag {
    asset = Wwwision.Neos.DummyImage:DummyImage {
        width = 600
        height = 500
    }
}
```

Will render an image like this:

![Dummy image, unmodified](/ExampleImage.svg "Dummy image, unmodified")

Resizing works just like with regular images:

```
someImage = Neos.Neos:ImageTag {
    asset = Wwwision.Neos.DummyImage:DummyImage {
        width = 600
        height = 500
    }
    maximumWidth = 500
    maximumHeight = 450
}
```

![Dummy image, resized](/ExampleImage_resized.svg "Dummy image, resized")

..and so does cropping

```
someImage = Neos.Neos:ImageTag {
    asset = Wwwision.Neos.DummyImage:DummyImage {
        width = 600
        height = 500
    }
    width = 500
    height = 450
    allowCropping = true
}
```

![Dummy image, resized and cropped](/ExampleImage_cropped.svg "Dummy image, resized and cropped")

### Example: Responsive images with Atomic Fusion

This package can be used together with [Atomic Fusion](https://www.neos.io/blog/atomic-fusion.html) components
allowing them to centralize resizing/cropping logic.
An implementation of a `ResponsiveImage` atom could look something like this:

```
prototype(Your.Package:Component.Atom.ResponsiveImage) < prototype(PackageFactory.AtomicFusion:Component) {

    @styleguide {
        title = 'Responsive Image'
        description = 'Image with alternative sizes for different breakpoints'

        props {
            image = Wwwision.Neos.DummyImage:DummyImage {
                width = 1000
                height = 800
            }
        }
        propSets {
            'flexible width and height' {
                width = 400
                height = 350
                altText = 'Lorem ipsum dolor'
                allowCropping = true
                responsiveImages = Neos.Fusion:RawArray {
                    large = Neos.Fusion:RawArray {
                        minWidth = 1025
                        imageWidth = 600
                        imageHeight = 500
                    }
                    medium = Neos.Fusion:RawArray {
                        minWidth = 769
                        imageWidth = 500
                        imageHeight = 420
                    }
                }
            }
            'fixed height' {
                width = 400
                height = 350
                allowCropping = true
                responsiveImages = Neos.Fusion:RawArray {
                    large = Neos.Fusion:RawArray {
                        minWidth = 1025
                        imageWidth = 600
                    }
                    medium = Neos.Fusion:RawArray {
                        minWidth = 769
                        imageWidth = 500
                    }
                }
            }
            'fixed width' {
                width = 400
                height = 350
                allowCropping = true
                responsiveImages = Neos.Fusion:RawArray {
                    large = Neos.Fusion:RawArray {
                        minWidth = 1025
                        imageHeight = 500
                    }
                    medium = Neos.Fusion:RawArray {
                        minWidth = 769
                        imageHeight = 420
                    }
                }
            }
        }
    }

    # API
    image = null
    width = null
    height = null
    altText = null
    allowCropping = true
    responsiveImages = Neos.Fusion:RawArray
    classNames = null
    # /API

    renderer.@context {
        _sourceSets = Neos.Fusion:Collection {
            collection = ${props.responsiveImages}
            itemName = 'responsiveImage'
            itemRenderer = Neos.Fusion:Value {
                @context.responsiveImageUri = Neos.Neos:ImageUri {
                    asset = ${props.image}
                    width = ${responsiveImage.imageWidth ? responsiveImage.imageWidth : props.width}
                    height = ${responsiveImage.imageHeight ? responsiveImage.imageHeight : props.height}
                    allowCropping = ${props.allowCropping}
                }
                value = ${'<source srcset="' + responsiveImageUri + '" media="(min-width: ' + responsiveImage.minWidth + 'px)">'}
            }
        }
        _defaultImageUri = Neos.Neos:ImageUri {
            asset = ${props.image}
            width = ${props.width}
            height = ${props.height}
            allowCropping = ${props.allowCropping}
        }
    }

    renderer = afx`
        <picture class={props.classNames}>
            {_sourceSets}
            <img srcset={_defaultImageUri} alt={props.altText} />
        </picture>
    `
}

```

## License

Licensed under GPLv3+, see [LICENSE](LICENSE)
