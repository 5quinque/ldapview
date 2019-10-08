<?php

namespace App\Service;

class LdapService
{
    public function setCriteria(array $criteria = [])
    {
        $ou = "";
        $objectClass = "";
        
        if (isset($criteria["ou"])) {
            $ou = "ou={$criteria["ou"]},";
        }
        if (isset($criteria["objectClass"])) {
            $objectClass = "objectClass={$criteria["objectClass"]}";
        }

        return ["ou" => $ou, "objectClass" => $objectClass];
    }
}