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
                $manager = $modx->getManager();
                $level = $modx->getLogLevel();
                $modx->setLogLevel(xPDO::LOG_LEVEL_FATAL);

                if (!array_key_exists('msProductData', $modx->map)) {
                    include MODX_CORE_PATH . 'components/minishop2/model/minishop2/mysql/msproductdata.map.inc.php';
                    $modx->map['msProductData'] = $xpdo_meta_map['msProductData'];
                }
                if (!array_key_exists('mscdek_size', $modx->map['msProductData']['fields'])) {
                    $modx->map['msProductData']['fields']['mscdek_size'] = null;
                    $modx->map['msProductData']['fieldMeta']['mscdek_size'] = [
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => true,
                        'default' => '',
                    ];
                }
                $manager->addField('msProductData', 'mscdek_size');
                if ($setting = $modx->getObject('modSystemSetting', ['key' => 'ms2_product_extra_fields'])) {
                    $value = explode(',', $setting->value);
                    if (!in_array('mscdek_size', $value)) {
                        $value[] = 'mscdek_size';
                        $setting->set('value', implode(',', $value));
                        $setting->save();
                    }
                }

                $modx->setLogLevel($level);
                
                $table = $modx->getTableName('msOrderAddress');
                $sql = 'ALTER TABLE ' . $table . '  ADD `point` VARCHAR(255) NULL;';
                $modx->exec($sql);
                $modx->log(3, 'Добавлено поле point в таблицу msOrderAddress');
                
                $fields = ['cdek_id', 'inner_cdek_id', 'cdek_tariff_id'];
    
                foreach ($fields as $item) {
                    $table = $modx->getTableName('msOrderAddress');
                    $sql = "ALTER TABLE $table  ADD $item int(10) NULL;";
                    $modx->exec($sql);
                    $modx->log(3, "Добавлено поле <b>$item</b> в $table");
                }
    
                $table = $modx->getTableName('msOrderAddress');
                $sql = "ALTER TABLE $table  ADD pvz_id varchar(255) NULL;";
                $modx->exec($sql);
                
                $miniShop2->addPlugin('ms_CDEK2', '{core_path}components/ms_cdek2/model/ms2/index.php');
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            if ($miniShop2 = $modx->getService('miniShop2')) {
                $miniShop2->removePlugin('ms_CDEK2');
            }
            break;
    }
}

return true;