<?php

class Customer extends CustomerCore
{
    /** @var string phone */
    public $phone;
    
    public function __construct($idStore = null, $idLang = null)
    {
        Self::$definition['fields']['phone']=array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 64);
        parent::__construct($idStore, $idLang);
    }


}