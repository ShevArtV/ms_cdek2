<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var ms_CDEK2 $ms_CDEK2 */
$ms_CDEK2 = $modx->getService('ms_CDEK2', 'ms_CDEK2', MODX_CORE_PATH . 'components/ms_cdek2/model/');
$modx->lexicon->load('ms_cdek2:default');

// handle request
$corePath = $modx->getOption('ms_cdek2_core_path', null, $modx->getOption('core_path') . 'components/ms_cdek2/');
$path = $modx->getOption('processorsPath', $ms_CDEK2->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);