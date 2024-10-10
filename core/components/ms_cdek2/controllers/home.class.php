<?php

/**
 * The home manager controller for ms_CDEK2.
 *
 */
class ms_CDEK2HomeManagerController extends modExtraManagerController
{
    /** @var ms_CDEK2 $ms_CDEK2 */
    public $ms_CDEK2;


    /**
     *
     */
    public function initialize()
    {
        $this->ms_CDEK2 = $this->modx->getService('ms_CDEK2', 'ms_CDEK2', MODX_CORE_PATH . 'components/ms_cdek2/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['ms_cdek2:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('ms_cdek2');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->ms_CDEK2->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/ms_cdek2.js');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->ms_CDEK2->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        ms_CDEK2.config = ' . json_encode($this->ms_CDEK2->config) . ';
        ms_CDEK2.config.connector_url = "' . $this->ms_CDEK2->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "ms_cdek2-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="ms_cdek2-panel-home-div"></div>';

        return '';
    }
}