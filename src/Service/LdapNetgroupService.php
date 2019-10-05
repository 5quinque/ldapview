<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use Doctrine\Common\Persistence\ObjectManager;
use App\Service\LdapPeopleService;
use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;
use App\Entity\Netgroup;

class LdapNetgroupService
{
    private $ldap;

    public function __construct(
        ObjectManager $objectManager,
        NetgroupRepository $netgroupRepository,
        PeopleRepository $peopleRepository,
        LdapPeopleService $ldapPeopleService
    ) {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_HOST'],
            'encryption' => 'none',
        ]);

        $this->ldap->bind($_ENV['LDAP_USER'], $_ENV['LDAP_PASS']);

        $this->om = $objectManager;
        $this->netgroupRepository = $netgroupRepository;
        $this->peopleRepository = $peopleRepository;
        $this->ldapPeopleService = $ldapPeopleService;
    }

    public function persist(Netgroup $netgroup)
    {
        $entryManager = $this->ldap->getEntryManager();
        $query = $this->ldap->query(
            'ou=netgroup,dc=example,dc=org',
            "(&(ObjectClass=nisNetgroup)(uid={$netgroup->getName()}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();

        $entry = $results[0];
        $entry->setAttribute('description', [$netgroup->getDescription()]);
        // todo add/remove people

        $entryManager->update($entry);
    }

    public function createNetgroupEntity(object $ldap_netgroup): object
    {
        $netgroup = new Netgroup();

        return $this->updateNetgroupEntity($netgroup, $ldap_netgroup);
    }

    public function updateNetgroupEntity(Netgroup $netgroup, object $ldap_netgroup): object
    {
        $netgroup->setName(
            current($ldap_netgroup->getAttributes()["cn"])
        );

        if (isset($ldap_netgroup->getAttributes()["description"])) {
            $netgroup->setDescription(
                current($ldap_netgroup->getAttributes()["description"])
            );
        }
        
        if (isset($ldap_netgroup->getAttributes()["nisNetgroupTriple"])) {
            $this->addUsers($netgroup, $ldap_netgroup->getAttributes()["nisNetgroupTriple"]);
        }
        if (isset($ldap_netgroup->getAttributes()["memberNisNetgroup"])) {
            $this->addChildNetgroups($netgroup, $ldap_netgroup->getAttributes()["memberNisNetgroup"]);
        }

        $this->om->persist($netgroup);
        $this->om->flush();
        
        return $netgroup;
    }

    public function findOneByNetgroup(string $name): ?object
    {
        $query = $this->ldap->query(
            'ou=netgroup,dc=example,dc=org',
            "(&(structuralObjectClass=nisNetgroup)(cn={$name}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();
        if ($results->count() === 0) {
            return null;
        }

        return current($results->toArray());
    }

    public function findAll(array $criteria = [])
    {
        $ou = "";
        $objectClass = "";

        if (isset($criteria["ou"])) {
            $ou = "ou={$criteria["ou"]},";
        }
        if (isset($criteria["objectClass"])) {
            $objectClass = "objectClass={$criteria["objectClass"]}";
        }

        $query = $this->ldap->query(
            "{$ou}dc=example,dc=org",
            $objectClass,
            [
                "pageSize" => 10,
            ]
        );
        $results = $query->execute();

        foreach ($results as $ldap_netgroup) {
            $netgroup = $this->netgroupRepository->findOneBy(array('name' => $ldap_netgroup->getAttributes()["cn"]));

            if (!$netgroup) {
                $this->createNetgroupEntity($ldap_netgroup);
            } else {
                $this->updateNetgroupEntity($netgroup, $ldap_netgroup);
            }
        }
        
        return $results;
    }

    private function addChildNetgroups(Netgroup $netgroup, array $netgroups)
    {
        // Add these users to netgroup
        foreach ($netgroups as $nis) {
            $childNetgroup = $this->netgroupRepository->findOneBy(array('name' => $nis));

            // If user isn't in database, check LDAP and create new entity
            if (is_null($childNetgroup)) {
                $ldap_child = $this->findOneByNetgroup($nis);
                if (is_null($ldap_child)) {
                    continue;
                }
                $childNetgroup = $this->createNetgroupEntity($ldap_child);
            }
            $netgroup->addChildNetgroup($childNetgroup);
        }
    }

    private function addUsers(Netgroup $netgroup, array $people)
    {
        // Add these users to netgroup
        foreach ($people as $nis) {
            preg_match('/^\(,(.+),.+\)$/', $nis, $matches);
            if (empty($matches)) {
                continue;
            }
            
            $person = $this->peopleRepository->findOneBy(array('uid' => $matches[1]));

            // If user isn't in database, check LDAP and create new entity
            if (is_null($person)) {
                $ldap_person = $this->ldapPeopleService->findOneByUid($matches[1]);
                if (is_null($ldap_person)) {
                    continue;
                }
                $person = $this->ldapPeopleService->createPersonEntity($ldap_person);
            }
            $netgroup->addPerson($person);
        }
    }
}
