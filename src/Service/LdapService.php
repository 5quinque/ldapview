<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;
use App\Service\LdapPeopleService;
use App\Service\LdapNetgroupService;
use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;

class LdapService
{
    private $ldap;

    public function __construct(LdapPeopleService $ldapPeopleService,
    LdapNetgroupService $ldapNetgroupService,
    NetgroupRepository $netgroupRepository,
    PeopleRepository $peopleRepository
    )
    {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_HOST'],
            'encryption' => 'none',
        ]);

        $this->ldap->bind($_ENV['LDAP_USER'], $_ENV['LDAP_PASS']);

        $this->ldapPeopleService = $ldapPeopleService;
        $this->ldapNetgroupService = $ldapNetgroupService;
        $this->netgroupRepository = $netgroupRepository;
        $this->peopleRepository = $peopleRepository;
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
        switch ($type) {
            case 'ou=people,':
                $entity = $this->peopleRepository->findOneBy(array('uid' => $ldap_result->getAttributes()["uid"]));
                $this->ldapPeopleService->updatePersonEntity($entity, $ldap_result);
                break;
            case 'ou=netgroup,':
                $entity = $this->netgroupRepository->findOneBy(array('name' => $ldap_result->getAttributes()["cn"]));
                $this->ldapNetgroupService->updateNetgroupEntity($entity, $ldap_result);
                break;
        }
    }
}