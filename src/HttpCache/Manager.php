<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.04.20.
 * Time: 9:35
 */

namespace Webtown\KunstmaanExtensionBundle\HttpCache;

use FOS\HttpCacheBundle\CacheManager;
use Symfony\Component\Filesystem\Filesystem;

class Manager
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * For the future...
     *
     * @var CacheManager
     */
    protected $fosHttpCacheManager;

    /**
     * Manager constructor.
     * @param string $cacheDir
     * @param Filesystem $filesystem
     * @param CacheManager $fosHttpCacheManager
     */
    public function __construct($cacheDir, Filesystem $filesystem, CacheManager $fosHttpCacheManager = null)
    {
        $this->cacheDir = $cacheDir;
        $this->filesystem = $filesystem;
        $this->fosHttpCacheManager = $fosHttpCacheManager;
    }

    public function forcePurgeAll()
    {
        $cacheDir = $this->cacheDir . DIRECTORY_SEPARATOR . 'http_cache';
        if ($this->filesystem->exists($cacheDir) && is_dir($cacheDir)) {
            $this->filesystem->remove(new \FilesystemIterator($cacheDir));
        }
    }
}
