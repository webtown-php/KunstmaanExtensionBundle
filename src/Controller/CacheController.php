<?php

namespace Webtown\KunstmaanExtensionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CacheController extends Controller
{
    /**
     * @Route("/index", name="KunstmaanAdminBundle_cache")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/purge", name="KunstmaanAdminBundle_cache_purge")
     */
    public function purgeAction()
    {
        try {
            $this->get('webtown_kunstmaan_extension.cache_manager')->forcePurgeAll();
            $this->addFlash('success', $this->get('translator')->trans('kuma_admin.cache.flash.cache_purged'));
        } catch (\Exception $e) {
            $this->addFlash('error', $this->get('translator')->trans(
                'kuma_admin.cache.flash.cache_not_purged.%exception%',
                ['%exception%' => $e->getMessage()]
            ));
        }

        return $this->redirectToRoute('KunstmaanAdminBundle_cache');
    }
}
