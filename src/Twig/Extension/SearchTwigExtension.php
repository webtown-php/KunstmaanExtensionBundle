<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.05.
 * Time: 15:23
 */

namespace Webtown\KunstmaanExtensionBundle\Twig\Extension;


use Symfony\Bridge\Twig\Extension\RoutingExtension;

class SearchTwigExtension extends \Twig_Extension
{
    /**
     * @var RoutingExtension
     */
    protected $routingExtension;

    /**
     * SearchTwigExtension constructor.
     * @param RoutingExtension $routingExtension
     */
    public function __construct(RoutingExtension $routingExtension)
    {
        $this->routingExtension = $routingExtension;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('kuma_search_result_url', [$this, 'getSearchResultUrl']),
        ];
    }

    public function getSearchResultUrl($result)
    {
        if (!is_array($result) || !array_key_exists('_source', $result)) {
            throw new \InvalidArgumentException();
        }

        $source = $result['_source'];
        if (array_key_exists('slug', $source)) {
            return $this->routingExtension->getPath('_slug', ['url' => $source['slug']]);
        } elseif (array_key_exists('route_name', $source) && array_key_exists('route_parameters', $source)) {
            return $this->routingExtension->getPath($source['route_name'], $source['route_parameters']);
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'wt_kuma_extension.search';
    }
}
