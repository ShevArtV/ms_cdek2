<?php

class ms_CDEK2ItemUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType = 'ms_CDEK2Item';
    public $classKey = 'ms_CDEK2Item';
    public $languageTopics = ['ms_cdek2'];
    //public $permission = 'save';


    /**
     * We doing special check of permission
     * because of our objects is not an instances of modAccessibleObject
     *
     * @return bool|string
     */
    public function beforeSave()
    {
        if (!$this->checkPermissions()) {
            return $this->modx->lexicon('access_denied');
        }

        return true;
    }


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $name = trim($this->getProperty('name'));
        if (empty($id)) {
            return $this->modx->lexicon('ms_cdek2_item_err_ns');
        }

        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ms_cdek2_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name, 'id:!=' => $id])) {
            $this->modx->error->addField('name', $this->modx->lexicon('ms_cdek2_item_err_ae'));
        }

        return parent::beforeSet();
    }
}

return 'ms_CDEK2ItemUpdateProcessor';
