<?php
namespace Fox;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;
use Twig\TwigFilter;
use Twig_SimpleFilter;

class Template extends FilesystemLoader {

    public function __construct($paths = [], $rootPath = null) {
        parent::__construct($paths, $rootPath);
    }

    private $cache_enabled = true;

    /**
     * @param $path
     * @return TemplateWrapper|null
     */
    public function load($path) {
        try {
            $twig = new \Twig\Environment($this, !$this->cache_enabled ? [] : ['cache' => 'app/cache']);

            $twig->addFunction(new \Twig\TwigFunction('url', function ($string, $internal = true) {
                return $internal ? web_root . $string : $string;
            }));

            $twig->addFunction(new \Twig\TwigFunction('stylesheet', function ($string) {
                return web_root.'public/css/' . $string . '';
            }));

            $twig->addFunction(new \Twig\TwigFunction('javascript', function ($string) {
                return web_root . 'public/js/' . $string . '';
            }));

            $twig->addFunction(new \Twig\TwigFunction('constant', function ($string) {
                return constant($string);
            }));

            $twig->addFunction(new \Twig\TwigFunction('curdate', function ($string) {
                return date($string);
            }));

            $twig->addFunction(new \Twig\TwigFunction('debugArr', function ($string) {
                return json_encode($string, JSON_PRETTY_PRINT);
            }));

            $twig->addFunction(new \Twig\TwigFunction('in_array', function ($needle, $haystack) {
                return in_array($needle, $haystack);
            }));

            $twig->addFilter(new TwigFilter('array_chunk', function($array, $limit) {
                return array_chunk($array, $limit);
            }));

            return $twig->load($path . '.twig');
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            return null;
        }
    }

    public function setCacheEnabled($val) {
        $this->cache_enabled = $val;
    }
}