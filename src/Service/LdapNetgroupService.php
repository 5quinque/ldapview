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

    public function persist(Netgroup $netgroup, array $previousPeople)
    {
        $entryManager = $this->ldap->getEntryManager();
        $query = $this->ldap->query(
            "ou=netgroup,{$_ENV['LDAP_DC']}",
            "(&(ObjectClass=nisNetgroup)(cn={$netgroup->getName()}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();

        $entry = $results[0];
        $entry->setAttribute('description', [$netgroup->getDescription()]);

        // todo test add/remove people
        $this->removeNetgroupFromUsers($netgroup->getName(), $netgroup->getPeople(), $previousPeople);

        $entryManager->update($entry);
    }

    public function persistPerson(People $person, array $previousNetgroups)
    {
        $entryManager = $this->ldap->getEntryManager();
        $uid = $person->getUid();

        // Add user to netgroups
        foreach ($person->getNetgroup() as $netgroup) {
            $name = $netgroup->getName();
            $query = $this->ldap->query(
                "ou=netgroup,{$_ENV['LDAP_DC']}",
                "(&(structuralObjectClass=nisNetgroup)(cn={$name}))",
                ["maxItems" => 1]
            );

            $results = $query->execute();
            $entry = $results[0];
            $NG_UIDs = $entry->getAttribute('nisNetgroupTriple');

            // If user not already in netgroup, add it
            if (!count(preg_grep("/^\(,{$uid},.+\)$/", $NG_UIDs))) {
                $NG_UIDs[] = "(,{$uid},bskyb.com)";
                $entry->setAttribute('nisNetgroupTriple', $NG_UIDs);
            }

            $entryManager->update($entry);
        }

        $this->removeUserFromNetgroups($uid, $person->getNetgroup(), $previousNetgroups);
    }

    public function removeNetgroupFromUsers(string $name, object $currentPeople, array $previousPeople)
    {
        // Convert `$currentNetgroups` into just an array of ids
        $currentPeopleIds = [];
        foreach ($currentPeople as $person) {
            $currentPeopleIds[] = $person->getId();
        }

        // Remove any users from netgroup
        $peopleToRemove = array_diff($previousPeople, $currentPeopleIds);
        foreach ($peopleToRemove as $personID) {
            $person = $this->peopleRepository->findOneBy(["id" => $personID]);
            $this->removeUserFromNetgroup($person->getId(), $name);
        }
    }

    public function removeUserFromNetgroups(string $uid, object $currentNetgroups, array $previousNetgroups)
    {
        // Convert `$currentNetgroups` into just an array of ids
        $currentNetgroupsIds = [];
        foreach ($currentNetgroups as $netgroup) {
            $currentNetgroupsIds[] = $netgroup->getId();
        }

        // Remove user from any netgroups they have been removed from
        $netgroupsToRemove = array_diff($previousNetgroups, $currentNetgroupsIds);
        foreach ($netgroupsToRemove as $netgroupID) {
            $netgroup = $this->netgroupRepository->findOneBy(["id" => $netgroupID]);
            $this->removeUserFromNetgroup($uid, $netgroup->getName());
        }
    }

    public function removeUserFromNetgroup($uid, $netgroup)
    {
        $entryManager = $this->ldap->getEntryManager();
        $query = $this->ldap->query(
            "ou=netgroup,{$_ENV['LDAP_DC']}",
            "(&(structuralObjectClass=nisNetgroup)(cn={$netgroup}))",
            ["maxItems" => 1]
        );
        $results = $query->execute();
        $entry = $results[0];
        
        $peopleInNetgroup = $entry->getAttribute('nisNetgroupTriple');
        $peopleWithoutUid = array_diff($peopleInNetgroup, ["(,{$uid},bskyb.com)"]);
        $entry->setAttribute('nisNetgroupTriple', $peopleWithoutUid);
        $entryManager->update($entry);

        // Figure out how to get this to work..?
        //$entryManager->removeAttributeValues($entry, 'nisNetgroupTriple', ["(,{$uid},bskyb.com)"]);
    }

    public function createNetgroupEntity(object $ldap_netgroup): object
    {
        $netgroup = new Netgroup();

        return $this->updateNetgroupEntity($netgroup, $ldap_netgroup);
    }

    public function updateNetgroupEntity(Netgroup $netgroup, object $ldap_netgroup): object
    {
        if (!$netgroup) {
            $netgroup = new Netgroup();
        }
        dump($netgroup->getName());
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
            "(&(structuralObjectClass=nisNetgroup)(cn={$name}))",
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
            $childNetgroup = $this->netgroupRepository->findOneBy(array('name' => $nis));

            // If user isn't in database, check LDAP and create new entity
            if (is_null($childNetgroup)) {
                $ldap_child = $this->findOneByNetgroup($nis);
                if (is_null($ldap_child)) {
                    // User can not be found in LDAP
                    continue;
                }
                $childNetgroup = $this->createNetgroupEntity($ldap_child);
            }
            $netgroup->addChildNetgroup($childNetgroup);
        }
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
           
            // If user isn't in database, or LDAP, move on
            if (is_null($person) && is_null($ldap_person)) {
                continue;
            }

            $person = $this->ldapPeopleService->updatePersonEntity($person, $ldap_person);
            $netgroup->addPerson($person);
        }
    }
}
