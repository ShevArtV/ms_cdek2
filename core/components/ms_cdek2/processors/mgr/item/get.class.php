<?php

class ms_CDEK2ItemGetProcessor extends modObjectGetProcessor
{
    public $objectType = 'ms_CDEK2Item';
    public $classKey = 'ms_CDEK2Item';
    public $languageTopics = ['ms_cdek2:default'];
    //public $permission = 'view';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return mixed
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        return parent::process();
    }

}

return 'ms_CDEK2ItemGetProcessor';