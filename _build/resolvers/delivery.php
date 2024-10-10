<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            if ($miniShop2 = $modx->getService('miniShop2')) {
                $miniShop2->addService('delivery', 'msCDEKHandler',
                    '{core_path}components/ms_cdek2/model/ms2/mscdekhandler.class.php'
                );
                $miniShop2->addService('delivery', 'msCDEKHandlerPVZ',
                    '{core_path}components/ms_cdek2/model/ms2/mscdekhandlerpvz.class.php'
                );
                
                if (!$msCDEKHandler = $modx->getObject('msDelivery', ['class' => 'msCDEKHandler'])) {
                    $msCDEKHandler = $modx->newObject('msDelivery');
                    $msCDEKHandler->fromArray([
                        'name' => 'CDEK до двери',
                        'logo' => MODX_ASSETS_URL . 'components/ms_cdek2/img/cdek-courier.png',
                        'active' => 0,
                        'class' => 'msCDEKHandler',
                        'requires' => 'receiver,email,phone,index,city,street,building,room',
                    ]);
                    $msCDEKHandler->save();
                }
                
                if (!$msCDEKHandlerPVZ = $modx->getObject('msDelivery', ['class' => 'msCDEKHandlerPVZ'])) {
                    $msCDEKHandlerPVZ = $modx->newObject('msDelivery');
                    $msCDEKHandlerPVZ->fromArray([
                        'name' => 'CDEK на пункт выдачи заказа',
                        'logo' => MODX_ASSETS_URL . 'components/ms_cdek2/img/cdek-point.png',
                        'active' => 0,
                        'class' => 'msCDEKHandlerPVZ',
                        'requires' => 'receiver,email,phone,index,city,point',
                    ]);
                    $msCDEKHandlerPVZ->save();
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            if ($miniShop2 = $modx->getService('miniShop2')) {
                $miniShop2->removeService('delivery', 'msCDEKHandler');
                $miniShop2->removeService('delivery', 'msCDEKHandlerPVZ');
            }
            break;
    }
}

return true;