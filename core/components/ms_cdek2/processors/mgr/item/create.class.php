<?php

class ms_CDEK2ItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'ms_CDEK2Item';
    public $classKey = 'ms_CDEK2Item';
    public $languageTopics = ['ms_cdek2'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ms_cdek2_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('ms_cdek2_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'ms_CDEK2ItemCreateProcessor';