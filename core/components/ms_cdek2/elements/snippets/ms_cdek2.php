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

$frontend_js = $modx->getOption('frontend_js', $scriptProperties, '{assets_url}components/ms_cdek2/js/web/mscdek.js');
$frontend_js = str_replace('{assets_url}', MODX_ASSETS_URL, $frontend_js);

$modx->regClientScript('https://www.cdek.ru/website/edostavka/template/js/widjet.js');
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
$defaultCityData = $ms_CDEK2->getDeliveryCity();
$defaultCity = $defaultCityData['city'];
$servicepath = str_replace('{assets_url}', MODX_ASSETS_URL, $modx->getOption('ms_cdek2_servicepath'));
$mainStylePath = str_replace('{assets_url}', MODX_ASSETS_URL, $modx->getOption('ms_cdek2_main_css_path', '', '{assets_url}components/ms_cdek2/css/web/main.css'));
$autocompleteStylePath = str_replace('{assets_url}', MODX_ASSETS_URL, $modx->getOption('ms_cdek2_autocomplete_css_path', '', '{assets_url}components/ms_cdek2/css/web/suggestions.css'));
$templatepath = MODX_ASSETS_URL . 'components/ms_cdek2/widjet/template.php';
$modx->regClientHTMLBlock("<script>
window.mscdek_config = {
    MainHandler: {
        pathToScripts: './modules/mainhandler.js',
        stylePath: '$mainStylePath',
        hideClass: 'hide',
        actionUrl: '$action_url',
        deliveries: {$modx->toJSON($deliveries)},
        points: {$modx->toJSON($points)},
        statusId: '#ms_cdek2_status',
        mapId: '#ms_cdek2_map',        
        widjet: {
            defaultCity: '{$defaultCity}',
            cityFrom: '{$ms_CDEK2->getSenderCity()}',
            link: 'ms_cdek2_map',
            hidedelt: true,
            path: 'https://www.cdek.ru/website/edostavka/template/scripts/',
            servicepath: '$servicepath',
            templatepath: '$templatepath'
        }
    },
    AutoComplete: {
        pathToScripts: './modules/autocomplete.js',
        stylePath: '$autocompleteStylePath',
    }
}
</script>");
$modx->regClientScript($frontend_js);