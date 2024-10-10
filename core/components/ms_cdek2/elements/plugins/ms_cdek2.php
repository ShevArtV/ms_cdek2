<?php
/** @var modX $modx */
switch ($modx->event->name) {
    case 'msOnBeforeCreateOrder':
        $address = $msOrder->getOne('Address');
        $properties = $address->get('properties');
        
        $post_fields = ['point', 'pvz_id'];
        foreach ($post_fields as $field) {
            if (!empty($_POST[$field])) {
                $properties[$field] = strip_tags($_POST[$field]);
                $address->set($field, $_POST[$field]);
            }
        }

        $session_fields = ['cdek_id', 'cdek_tariff_id'];
        foreach ($session_fields as $field) {
            if (!empty($_SESSION['ms_CDEK2'][$field])) {
                $properties[$field] = strip_tags($_SESSION['ms_CDEK2'][$field]);
                $address->set($field, $_SESSION['ms_CDEK2'][$field]);
            }
        }
        $address->set('properties', $properties);
        $address->save();

        break;
    case 'msOnManagerCustomCssJs':
        if ($page != 'orders') return;
    	$modx->controller->addLastJavascript(MODX_ASSETS_URL . 'components/ms_cdek2/js/mgr/orders/orders.window.js');
        break;
}