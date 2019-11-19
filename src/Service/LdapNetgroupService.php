<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use Doctrine\Common\Persistence\ObjectManager;
use App\Service\LdapService;
use App\Service\LdapPeopleService;
use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;
use App\Entity\Netgroup;
use App\Entity\People;

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

    public function createNetgroupEntity(object $ldap_netgroup): object
    {
        $netgroup = new Netgroup();

        return $this->updateNetgroupEntity($netgroup, $ldap_netgroup);
    }

    public function updateNetgroupEntity($netgroup, object $ldap_netgroup): object
    {
        if (!$netgroup) {
            $netgroup = new Netgroup();
        }

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
            "ou=netgroup,{$_ENV['LDAP_DC']}",
            "(&(objectClass=nisNetgroup)(cn={$name}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();
        if ($results->count() === 0) {
            return null;
        }

        return current($results->toArray());
    }

    private function addChildNetgroups(Netgroup $netgroup, array $netgroups)
    {
        // Add these users to netgroup
        foreach ($netgroups as $nis) {
            $this->addChildNetgroup($netgroup, $nis);
        }
    }

    private function addChildNetgroup(Netgroup $parentNetgroup, $childNetgroupName)
    {
        $childNetgroup = $this->netgroupRepository->findOneBy(array('name' => $childNetgroupName));

        // If netgroup isn't in database, check LDAP and create new entity
        if (is_null($childNetgroup)) {
            $ldap_child = $this->findOneByNetgroup($childNetgroupName);
            if (is_null($ldap_child)) {
                // User can not be found in LDAP
                return false;
            }
            $childNetgroup = $this->createNetgroupEntity($ldap_child);
        }

        $parentNetgroup->addChildNetgroup($childNetgroup);
    }

    public function addUsers(Netgroup $netgroup, array $people)
    {
        // Add these users to netgroup
        foreach ($people as $nis) {
            preg_match('/^\(,(.+),.+\)$/', $nis, $matches);
            if (empty($matches)) {
                continue;
            }

            // Get the person entity
            $person = $this->peopleRepository->findOneBy(array('uid' => $matches[1]));
            $ldap_person = $this->ldapPeopleService->findOneByUid($matches[1]);

            // If user isn't in LDAP, move on
            if (is_null($ldap_person) || is_null($person)) {
                continue;
            }

            // Only add the user if they already exist in our database
            // We won't search for the user in LDAP and add it

            $netgroup->addPerson($person);
        }
    }
}
