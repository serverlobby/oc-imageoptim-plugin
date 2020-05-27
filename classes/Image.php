<?php namespace Tlokuus\ImageOptim\Classes;

use Log;
use Request;
use View;
use System\Models\File;
use SystemException;

class Image
{
    private $file;
    private $alt_text;
    
    /*const BREAKPOINTS = [
        'xl' => 1200,
        'lg' => 992,
        'md' => 768,
        'sm' => 576,
        'xs' => 0
    ];*/

    const DEFAULT_WIDTHS = [1200, 992, 768, 576];

    //const SUPPORTED_SIZES = [16, 32, 64];

    const RATIO_MAPPING = [
        '1:1' => '1.0',
        '3:2' => '0.6666'
    ];
    
    public function __construct($file, ?string $alt_text = null)
    {
        $this->file = $file;

        if(is_null($file)) {
            Log::error('Trying to create null image ' . Request::fullUrl());
        }

        $this->alt_text = ($alt_text ?? (($file instanceof File) ? $file->description : '')) ?? '';
    }

    public function getTag(?int $width = null, array $options = []) : string
    {
        return $this->renderTag($width, null, $options);
    }

    public function getResponsiveTag(?array $available_widths = null, array $options = []) : string
    {
        $available_widths = $available_widths ?? self::DEFAULT_WIDTHS;
        rsort($available_widths);
       /* $defaultPath = $this->getPath(max($available_widths), $options);

        $srcset = array_map(
            function($w) use ($options) {
                return [$this->getPath($w, $options), $w];
            },
            $available_widths
        );*/

        return $this->renderTag(max($available_widths), $available_widths, $options);
/*
        $options['class'] = $class;
        $attributes = array_merge(['data-src' => $defaultPath, 'alt' => $this->alt_text, 'srcset' => $srcset], $options);
        return View::make('tlokuus.imageoptim::lazytag', ['attr' => $attributes])->render();*/
    }

    private function renderTag(?int $src_width, ?array $srcset_widths = null, array $options = [])
    {
        $options['data-src'] = $this->getPath($src_width, $options);
        $options['alt'] = $options['alt'] ?? $this->alt_text;

        if(!is_null($srcset_widths)) {
            $options['data-srcset'] = implode(',',
                array_map(function($w) use ($options) {
                    return $this->getPath($w, $options) . ' ' . $w . 'w';
                }, $srcset_widths)
            );

            $options['data-sizes'] = 'auto';
        }

        unset($options['ratio']);
        unset($options['keep_alpha']);

        $options['class'] = $options['class'] ?? '';
        $options['class'] .= ($options['class'] ? ' ' : '') . 'lazyload';

        return View::make('tlokuus.imageoptim::imgtag', ['attributes' => $options])->render();
    }


    public function getPath(?int $width = null, array $options = []) : string
    {
        $urlparams = [
            'w' => $width,
            'r' => $this->resolveRatio($options['ratio'] ?? null),
            'comp' => intval(!boolval($options['keep_alpha'] ?? false)),
        ];
        
        $path = $this->getOriginalPath();
        $querystring = http_build_query($urlparams);

        return $path . ($querystring ? '?' : '') . $querystring;
    }

    /**
     * Maps a ratio string to one actually recognized by the image server (i.e. "3:2" => "0.6666")
     * @param null|string $ratio_name 
     * @return null|string 
     * @throws SystemException When the ratio is unsupported
     */
    private function resolveRatio(?string $ratio_name) : ?string
    {
        if(is_null($ratio_name))
            return null;

        if(!array_key_exists($ratio_name, self::RATIO_MAPPING))
            throw new SystemException("[ImageOptim] '$ratio_name' is not a valid supported ratio");
        
        return self::RATIO_MAPPING[$ratio_name];
    }


    private function getOriginalPath() : string
    {
        if(is_null($this->file) || is_string($this->file)) {
            return strval($this->file);
        }

        return $this->file->getPath();
    }

    /**
     * Build the 'sizes' attribute of an image tag.
     * @param array $breakpoint_widths Array of displayed width per breakpoint (i.e. ['xs' => '100vw', 'md' => '350px'])
     * @return array Array of computed sizes per media query (i.e. ['(max-width: 300px) 100vh', '(min-width: 350px)'])
     */
    /*private function getResponsiveSizes(array $breakpoint_widths, int $default_size) : array
    {
        $sizes = [];
        foreach(self::BREAKPOINTS as $b) {
            if(!array_key_exists($b, $breakpoint_widths))
                continue;
            
            $selector = $this->getMediaSelector($b);
            $sizes[$b] = $selector . $breakpoint_widths[$b];
        }

        $xs = array_key_last(self::BREAKPOINTS);
        $sizes[$xs] = $sizes[$xs] ?? ($this->getMediaSelector($xs) . '100vh');

        return $sizes;
    }*/

    /**
     * Generates a Media Query Selector that triggers at a predefined breakpoint
     * @param string $breakpoint The breakpoint name
     * @param string $type The direction ('min' to target the breakpoint and upper or 'max' to target below breakpoint)
     * @return string The generated query string (i.e. : "(min-width: 768px) ")
     */
    /*private function getMediaSelector(string $breakpoint, string $type = 'min') : string
    {
        $size = self::BREAKPOINTS[$breakpoint] - intval($type === 'max');
        return '(' . $type . '-width: ' . $size . 'px) ';
    }*/

    /*private function closestSize(int $size) : int
    {
        $maxSize = -1;
        $closest_upper = null;
        foreach(self::SUPPORTED_SIZES as $s)
        {
            $maxSize = max($s, $maxSize);
            if ($s >= $size && (is_null($closest_upper) || $s < $closest_upper)) {
                $closest_upper = $s;
            }

            if($closest_upper === $size)
                return $closest_upper;
        }

        return $closest_upper ?? $maxSize;
    }*/

    /*private function decodeSize(string $size) : array
    {
        if(is_integer($size) || ends_with($size, 'px')) {
            return [intval($size), 'px'];
        }

        if(ends_with($size, 'vw')) {
            return [intval($size), 'vw'];
        }

        throw new SystemException("[ImageOptim] Unsupported size: $size");
    }*/
}
?>