<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;
    /** @var Office $office */
    if ($Office = $modx->getService('Office', 'Office', MODX_CORE_PATH . 'components/office/model/office/')) {
        if (!($Office instanceof Office)) {
            $modx->log(xPDO::LOG_LEVEL_ERROR, '[ms_CDEK2] Could not register paths for Office component!');

            return true;
        } elseif (!method_exists($Office, 'addExtension')) {
            $modx->log(xPDO::LOG_LEVEL_ERROR,
                '[ms_CDEK2] You need to update Office for support of 3rd party packages!');

            return true;
        }

        /** @var array $options */
        switch ($options[xPDOTransport::PACKAGE_ACTION]) {
            case xPDOTransport::ACTION_INSTALL:
            case xPDOTransport::ACTION_UPGRADE:
                $Office->addExtension('ms_CDEK2', '[[++core_path]]components/ms_cdek2/controllers/office/');
                $modx->log(xPDO::LOG_LEVEL_INFO, '[ms_CDEK2] Successfully registered ms_CDEK2 as Office extension!');
                break;

            case xPDOTransport::ACTION_UNINSTALL:
                $Office->removeExtension('ms_CDEK2');
                $modx->log(xPDO::LOG_LEVEL_INFO, '[ms_CDEK2] Successfully unregistered ms_CDEK2 as Office extension.');
                break;
        }
    }
}

return true;