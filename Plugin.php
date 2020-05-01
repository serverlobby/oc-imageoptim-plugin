<?php namespace Tlokuus\ImageOptim;

use Event;
use System\Classes\PluginBase;
use Tlokuus\ImageOptim\Classes\Image;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name' => 'Image Optim',
            'description' => 'Helper functions to serve resized and compressed images using serverlobby/imageserver',
            'author' => 'Tlokuus',
        ];
    }

    public function boot()
    {
        Event::listen('cms.page.beforeDisplay', function($controller, $page) {
            $controller->addJs('/plugins/tlokuus/imageoptim/assets/js/lazysizes.min.js');
        });

        Event::listen('backend.page.beforeDisplay', function($controller, $page) {
            $controller->addJs('/plugins/tlokuus/imageoptim/assets/js/lazysizes.min.js');
        });

        Event::listen('markdown.beforeParse', function($data) {
            $text = preg_replace_callback('/!\[(.*)\]\((.*)\)(?:\r\n|\r|\n)\*(.*)\*/m', function($matches){
                return 
                '<figure>'
                . (new Image($matches[2]))->getResponsiveTag(null, ['alt' => $matches[1]])
                . '<figcaption>' . e($matches[3]) . '</figcaption>'
                . '</figure>';

            }, $data->text);

            $text = preg_replace_callback('/!\[(.*)\]\((.*)\)/', function($matches){
                return (new Image($matches[2]))->getResponsiveTag(null, ['alt' => $matches[1]]);
            }, $data->text);
            
            $data->text = $text;
        });
     
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'rimg' => function($file, array $widths = [], array $options = []) {
                    return (new Image($file))->getResponsiveTag($widths, $options);
                },

                'img' => function($file, ?int $width, array $options = []) {
                    return (new Image($file))->getTag($width, $options);
                },

                'imgpath' => function($file, ?int $width, array $options = []) {
                    return (new Image($file))->getPath($width, $options);
                }
            ]
        ];
    }


    public function registerListColumnTypes()
    {
        dd(2);
        return [
            'thumb' => [$this, 'evalThumbListColumn'],
        ];
    }

    public function evalThumbListColumn($value, $column, $record)
    {
        // Inspired from https://github.com/toughdeveloper/oc-imageresizer-plugin/blob/master/Plugin.php

        // attachMany relation?
        if (isset($record->attachMany[$column->columnName]))
        {
            $file = $value->first();
        }
        // attachOne relation?
        else if (isset($record->attachOne[$column->columnName]))
        {
            $file = $value;
        }
        // Mediafinder
        else
        {
            $file = storage_path() . '/app/media' . $value;
        }

        $image = new Image($file);
        return $image->getTag(128, ['ratio' => '1:1']);
    }

}
