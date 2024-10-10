<?php

class ms_CDEK2ItemDisableProcessor extends modObjectProcessor
{
    public $objectType = 'ms_CDEK2Item';
    public $classKey = 'ms_CDEK2Item';
    public $languageTopics = ['ms_cdek2'];
    //public $permission = 'save';


    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $ids = $this->modx->fromJSON($this->getProperty('ids'));
        if (empty($ids)) {
            return $this->failure($this->modx->lexicon('ms_cdek2_item_err_ns'));
        }

        foreach ($ids as $id) {
            /** @var ms_CDEK2Item $object */
            if (!$object = $this->modx->getObject($this->classKey, $id)) {
                return $this->failure($this->modx->lexicon('ms_cdek2_item_err_nf'));
            }

            $object->set('active', false);
            $object->save();
        }

        return $this->success();
    }

}

return 'ms_CDEK2ItemDisableProcessor';
