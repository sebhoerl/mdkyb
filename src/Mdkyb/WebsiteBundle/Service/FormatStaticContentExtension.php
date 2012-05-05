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
    public function format($text, $board = false)
    {
        $router = $this->router;

        $parts = explode("### VORSTAND", $text);

        if ($board) {
            $text = isset($parts[1]) ? $parts[1] : $parts[0];
        } else {
            $text = $parts[0];
        }

        $text = str_replace(array('<', '>'),array('&lt;', '&gt;'), $text);

        $text = preg_replace('@^(\r?\n)+@', '', $text);
        $text = preg_replace('@(\r?\n)+$@', '', $text);

        $text = preg_replace('@\n\*(.*)@', '<ul><li>$1</li></ul>', $text);
        $text = preg_replace('@</ul>[ \n]*<ul>@', '', $text);

        $text = preg_replace('@\*([^\n]*?)\*@', '<em>$1</em>', $text);
        $text = preg_replace('@(.*)\n+(-{5,})@', '<h3>$1</h3>', $text);

        $text = preg_replace('@http://([^ \n]+)@', '<a href="http://$1">http://$1</a>', $text);
        $text = preg_replace('@\[([^\n]+?)\][ \n]*<a (.*?)>(.*?)</a>@', '<a $2>$1</a>', $text);

        $text = preg_replace('@([^\r\n])(\r?\n)([^\r\n])@', '$1 $2', $text);
        $text = preg_replace('@(\r?\n){3,}@', '<br /><br />', $text);
        $text = preg_replace('@(\r?\n)+@', '<br />', $text);

        $text = preg_replace('@</ul>[ \n]*<br />@', '</ul>', $text);

        return $text;
    }

    public function getFilters()
    {
        return array('format_content' => new Twig_Filter_Method($this, 'format'));
    }

    public function getName()
    {
        return 'format_static_content_extension';
    }
}
