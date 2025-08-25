<?php

namespace Fox;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TemplateWrapper;
use Twig\TwigFunction;
use Twig\TwigFilter;

class Template extends FilesystemLoader
{
    private bool $cache_enabled = true;

    public function __construct($paths = [], $rootPath = null)
    {
        parent::__construct($paths, $rootPath);
    }

    /**
     * Loads and renders a Twig template
     * @param string $path
     * @return TemplateWrapper|null
     */
    public function load(string $path): ?TemplateWrapper
    {
        try {
            $twig = new Environment($this, $this->cache_enabled
                ? ['cache' => 'app/cache']
                : []
            );

            $twig->addFunction(new TwigFunction('url', fn($string, $internal = true) =>
                $internal ? web_root . $string : $string
            ));

            $twig->addFunction(new TwigFunction('stylesheet', fn($string) =>
                web_root . 'public/css/' . $string
            ));

            $twig->addFunction(new TwigFunction('javascript', fn($string) =>
                web_root . 'public/js/' . $string
            ));

            $twig->addFunction(new TwigFunction('constant', fn($string) =>
                constant($string)
            ));

            $twig->addFunction(new TwigFunction('curdate', fn($format) =>
                date($format)
            ));

            $twig->addFunction(new TwigFunction('debugArr', fn($array) =>
                json_encode($array, JSON_PRETTY_PRINT)
            ));

            $twig->addFunction(new TwigFunction('in_array', fn($needle, $haystack) =>
                in_array($needle, $haystack)
            ));

            $twig->addFilter(new TwigFilter('array_chunk', fn($array, $limit) =>
                array_chunk($array, $limit)
            ));

            return $twig->load($path . '.twig');
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            return null;
        }
    }

    public function setCacheEnabled(bool $val): void
    {
        $this->cache_enabled = $val;
    }
}