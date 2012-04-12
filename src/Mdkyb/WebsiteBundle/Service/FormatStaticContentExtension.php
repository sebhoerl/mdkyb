<?php

namespace Mdkyb\WebsiteBundle\Service;

use Twig_Extension;
use Twig_Filter_Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Provies a Twig filter that formats static content
 */
class FormatStaticContentExtension extends Twig_Extension
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Formats static content
     * 
     * @param string $text Unformatted text
     * @return string HTML-formatted text
     */
    public function format($text)
    {
        $router = $this->router;

        return preg_replace_callback('/\[\[(.*?)\]\]/', function($match) use ($router) {
            list(, $slug) = $match;
            switch ($slug) {
                case '_index':
                    return $router->generate('index');
                case '_downloads':
                    return $router->generate('downloads');
                case '_jobs':
                    return  $router->generate('jobs');
                case '_gallery':
                    return $router->generate('gallery');
                default:
                    return $router->generate('static', array('slug' => trim($slug)));
            }
        }, $text);
    }

    public function getFilters()
    {
        return array('static_content' => new Twig_Filter_Method($this, 'format'));
    }

    public function getName()
    {
        return 'format_static_content_extension';
    }
}
