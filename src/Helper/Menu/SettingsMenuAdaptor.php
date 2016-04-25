<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.19.
 * Time: 14:10
 */

namespace Webtown\KunstmaanExtensionBundle\Helper\Menu;

use Kunstmaan\AdminBundle\Helper\Menu\MenuAdaptorInterface;
use Kunstmaan\AdminBundle\Helper\Menu\MenuBuilder;
use Kunstmaan\AdminBundle\Helper\Menu\MenuItem;
use Symfony\Component\HttpFoundation\Request;

class SettingsMenuAdaptor implements MenuAdaptorInterface
{
    /**
     * In this method you can add children for a specific parent, but also remove and change the already created children
     *
     * @param MenuBuilder $menu      The MenuBuilder
     * @param MenuItem[]  &$children The current children
     * @param MenuItem    $parent    The parent Menu item
     * @param Request     $request   The Request
     */
    public function adaptChildren(MenuBuilder $menu, array &$children, MenuItem $parent = null, Request $request = null)
    {
        if (is_null($parent)) {
            return;
        }

        if ('KunstmaanAdminBundle_settings' == $parent->getRoute()) {
            $menuItem = new MenuItem($menu);
            $menuItem
                ->setRoute('KunstmaanAdminBundle_cache')
                ->setLabel('kuma_admin.settings.cache.menu_label')
                ->setUniqueId('Cache')
                ->setParent($parent);
            if (stripos($request->attributes->get('_route'), $menuItem->getRoute()) === 0) {
                $menuItem->setActive(true);
                $parent->setActive(true);
            }
            $children[] = $menuItem;
        }
    }
}
