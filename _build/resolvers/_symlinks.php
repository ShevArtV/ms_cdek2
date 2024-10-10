<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/ms_CDEK2/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/ms_cdek2')) {
            $cache->deleteTree(
                $dev . 'assets/components/ms_cdek2/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/ms_cdek2/', $dev . 'assets/components/ms_cdek2');
        }
        if (!is_link($dev . 'core/components/ms_cdek2')) {
            $cache->deleteTree(
                $dev . 'core/components/ms_cdek2/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/ms_cdek2/', $dev . 'core/components/ms_cdek2');
        }
    }
}

return true;