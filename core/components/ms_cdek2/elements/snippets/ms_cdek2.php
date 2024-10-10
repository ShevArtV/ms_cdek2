<?php
/** @var modX $modx */
/** @var array $scriptProperties */
/** @var ms_CDEK2 $ms_CDEK2 */
$ms_CDEK2 = $modx->getService('ms_CDEK2', 'ms_CDEK2', MODX_CORE_PATH . 'components/ms_cdek2/model/', $scriptProperties);
if (!$ms_CDEK2) {
    return 'Could not load ms_CDEK2 class!';
}

/** @var miniShop2 $miniShop2 */
$miniShop2 = $modx->getService('miniShop2');
if (!$miniShop2) {
    return 'Could not load miniShop2 class!';
}

$frontend_js = $modx->getOption('frontend_js', $scriptProperties, '{assets_url}components/ms_cdek2/js/web/main.js');
$frontend_js = str_replace('{assets_url}', MODX_ASSETS_URL, $frontend_js);

$modx->regClientScript('https://www.cdek.ru/website/edostavka/template/js/widjet.js');
if ($modx->getOption('ms_cdek2_autocomplete')) {
    $modx->regClientScript(MODX_ASSETS_URL . 'components/ms_cdek2/js/web/vendor/auto-complete.min.js');
	$modx->regClientCSS(MODX_ASSETS_URL . 'components/ms_cdek2/js/web/vendor/auto-complete.css');	
}
$modx->regClientScript($frontend_js);

$deliveries = $points = [];
$msDeliveries = $modx->getCollection('msDelivery', ['class:IN' => ['msCDEKHandler', 'msCDEKHandlerPVZ']]);
foreach ($msDeliveries as $item) {
	if ($item->get('class') == 'msCDEKHandlerPVZ') {
	    $points[] = $item->get('id');
	} else {
	    $deliveries[] = $item->get('id');
	}
}

$action_url = MODX_ASSETS_URL . 'components/ms_cdek2/action.php';
$modx->regClientHTMLBlock('<script>
    ms_CDEK2.init({
        "action_url": "' . $action_url . '",
        "deliveries": ' . $modx->toJSON($deliveries) . ',
        "points": ' . $modx->toJSON($points) . ',
        "status_id": "#ms_cdek2_status",
        "map_id": "#ms_cdek2_map",
        "autocomplete": ' . $modx->getOption('ms_cdek2_autocomplete') . ',
        "widjet": {
            defaultCity: "' . $ms_CDEK2->getDeliveryCity() . '",
            cityFrom: "' . $ms_CDEK2->getSenderCity() . '",
            link: "ms_cdek2_map",
            hidedelt: true,
            path: "https://www.cdek.ru/website/edostavka/template/scripts/",
            servicepath: "' . str_replace('{assets_url}', MODX_ASSETS_URL, $modx->getOption('ms_cdek2_servicepath')) . '",
            templatepath: "' . MODX_ASSETS_URL . 'components/ms_cdek2/widjet/template.php",
        }
    });
</script>');