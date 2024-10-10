<?php

if(!class_exists('msDeliveryInterface')) {
    if (file_exists(MODX_CORE_PATH . 'components/minishop2/handlers/msdeliveryhandler.class.php')) {
        require_once(MODX_CORE_PATH . 'components/minishop2/handlers/msdeliveryhandler.class.php');
    } else {
        require_once dirname(dirname(dirname(__DIR__))) . '/minishop2/model/minishop2/msdeliveryhandler.class.php';
    }
}

if(!class_exists('msCDEKHandler')) {
    require_once __DIR__ . '/mscdekhandler.class.php';
}

class msCDEKHandlerPVZ extends msCDEKHandler implements msDeliveryInterface {
    
}