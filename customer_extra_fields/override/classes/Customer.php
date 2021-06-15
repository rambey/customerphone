<?php

class Customer extends CustomerCore{

    public $phone;

    public function __construct($id_customer = null, $full = false, $id_lang = null, $id_shop = null, \Context $context = null) {
        //Définition des nouveaux champs
        self::$definition['fields']['phone'] = [
            'type' => self::TYPE_STRING,
            'required' => false, 'size' => 255
        ];
        
        parent::__construct($id_customer, $full, $id_lang, $id_shop, $context);
    }
}
