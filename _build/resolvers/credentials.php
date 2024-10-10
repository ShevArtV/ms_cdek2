<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $move = [
                'cdek_auth_login' => 'ms_cdek2_login',
                'cdek_auth_password' => 'ms_cdek2_password',
                'cdek_senderCityPostCode' => 'ms_cdek2_sender_index',
            ];
            
            foreach ($move as $old_key => $new_key) {
                if ($old_setting = $modx->getObject('modSystemSetting', ['key' => $old_key])) {
                    $value = $old_setting->value;
                    if ($new_setting = $modx->getObject('modSystemSetting', ['key' => $new_key])) {
                        if (empty($new_setting->value)) {
                            $new_setting->set('value', $value);
                            $new_setting->save();
                        }
                    }
                }
            }
            
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;