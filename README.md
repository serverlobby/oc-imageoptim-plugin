# ImageOptim

ImageOptim provides helper functions to serve resized and compressed images using [ImageServer](https://github.com/serverlobby/imageserver) via AWS CloudFront.

The output img tag uses the lazyimages JS library.

## Available transformations
Transformation functions can take values in these range:

* **width:** `16, 32, 64, 128, 168, 290, 350, 510, 576, 768, 992, 1020, 1200`
* **ratio:** `"1:1", "3:2"`
* **keep_alpha:** `true` (blocks conversion of PNGs to JPGs in order to preserve transparency)

If the supplied width is not in the whitelist, will be rounded to the closest upper value.

## Twig filters

### imgpath(?int $width [,array $options])

```[html]
{{ server.logo|imgpath(100, {ratio: "3:2"}) }}
```

### img(?int width, [,array $options])
Outputs an <img> tag with the specified image transformation.
All options (other than ratio and keep_alpha) will be rendered as HTML attribute

```[html]
{{ server.logo|img(1200, {ratio: "3:2", class: "rounded", alt: "Alt text"}) }}
```

### rimg(array width = [], [,array $options])
Outputs an <img> tag with srcset furnished with multiple image widths and specified image transformation.
All options (other than ratio and keep_alpha) will be rendered as HTML attribute

```[html]
{{ server.logo|rimg([16,32,64]], {ratio: "3:2", class: "rounded", alt: "Alt text"}) }}
```