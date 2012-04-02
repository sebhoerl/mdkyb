<?php

namespace Mdkyb\WebsiteBundle\Service;

use Twig_Extension;
use Twig_Filter_Method;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Replaces [[slug :: label]] in static contents with the corresponding URL 
 */
class FormatStaticContentExtension extends Twig_Extension
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function format($text)
    {
        $router = $this->router;

        return preg_replace_callback('/\[\[(.*?)::(.*?)\]\]/', function($match) use ($router) {
            list(, $slug, $label) = $match;
            $url = $router->generate('static', array('slug' => trim($slug)));
            return sprintf('<a href="%s">%s</a>', trim($url), trim($label));
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
