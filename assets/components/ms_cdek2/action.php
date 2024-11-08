<?php

if (empty($_POST['action'])) {
    header("HTTP/1.1 403 Forbidden");
    exit('Access denied');
}

if (!file_exists(dirname(dirname(dirname(__DIR__))) . '/config.core.php')) {
    header("HTTP/1.1 500 Internal Server Error");
    exit('Server initialization error!');
}
define('MODX_API_MODE', true);

require_once dirname(dirname(dirname(__DIR__))) . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');
$modx->getService('error','error.modError', '', '');
$modx->lexicon->load('core:default');

$ms_CDEK2 = $modx->getService('ms_CDEK2', 'ms_CDEK2', MODX_CORE_PATH . 'components/ms_cdek2/model/', []);
if (!$ms_CDEK2) {
    return 'Could not load ms_CDEK2 class!';
}

$output = ['success' => false];

switch ($_POST['action']) {
    case 'getStatus':
        $ms_CDEK2->calc();
        $output['success'] = $_SESSION['ms_CDEK2']['success'];
        $output['status'] = $_SESSION['ms_CDEK2']['status'];
        break;
    case 'defaultCity':
        $output['success'] = true;
        $output['data'] = $ms_CDEK2->getDeliveryCity();
        break;
    case 'getPointAddress':
        $output = $ms_CDEK2->getPointAddress();
        break;
    default:
        header("HTTP/1.1 403 Forbidden");
        die();
        break;
}

@session_write_close();
exit(json_encode($output));
