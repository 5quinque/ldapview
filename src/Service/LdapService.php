<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;

class LdapService
{
    private $ldap;

    public function __construct()
    {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_HOST'],
            'encryption' => 'none',
        ]);

        $this->ldap->bind($_ENV['LDAP_USER'], $_ENV['LDAP_PASS']);
    }

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

    public function findAll(array $criteria = [])
    {
        $criteria = $this->setCriteria($criteria);

        $query = $this->ldap->query(
            "{$criteria['ou']}{$_ENV['LDAP_DC']}",
            $criteria["objectClass"],
            [
                "pageSize" => 10,
            ]
        );
        $results = $query->execute();

        foreach ($results as $ldap_result) {
            $this->persistEntity($criteria["ou"], $ldap_result);
        }
        
        return $results;
    }

    public function persistEntity(string $type, object $ldap_result)
    {
        if ($type == "people") {
            $entity = $this->peopleRepository->findOneBy(array('uid' => $ldap_result->getAttributes()["uid"]));
            if (!$person) {
                $this->createPersonEntity($ldap_result);
            } else {
                $this->updatePersonEntity($entity, $ldap_result);
            }
        } elseif ($type == "netgroup") {
            $entity = $this->netgroupRepository->findOneBy(array('name' => $ldap_result->getAttributes()["cn"]));
            if (!$entity) {
                $this->createNetgroupEntity($ldap_result);
            } else {
                $this->updateNetgroupEntity($entity, $ldap_result);
            }
        }
    }
}