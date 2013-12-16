<?php
class Fooman_Common_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function convertSerialToId($serial)
    {
        return hash('sha256',str_replace(array("\r\n", "\n", "\r"," ",PHP_EOL),'',$serial));
    }
}