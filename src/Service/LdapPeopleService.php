<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use Doctrine\Common\Persistence\ObjectManager;
use App\Service\LdapService;
use App\Repository\PeopleRepository;
use App\Entity\People;

class LdapPeopleService
{
    private $ldap;

    public function __construct(ObjectManager $objectManager, PeopleRepository $peopleRepository)
    {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_HOST'],
            'encryption' => 'none',
        ]);

        $this->ldap->bind($_ENV['LDAP_USER'], $_ENV['LDAP_PASS']);

        $this->om = $objectManager;
        $this->peopleRepository = $peopleRepository;
    }

    public function isDirectoryAdmin($uid)
    {
        $query = $this->ldap->query(
            "cn=Directory Administrators,{$_ENV['LDAP_DC']}",
            "uniqueMember=uid={$uid},ou=people,{$_ENV['LDAP_DC']}"
        );

        $results = $query->execute();

        return $results->count() === 1;
    }

    public function persist(People $person)
    {
        $entryManager = $this->ldap->getEntryManager();
        $query = $this->ldap->query(
            "ou=people,{$_ENV['LDAP_DC']}",
            "(&(ObjectClass=posixAccount)(uid={$person->getUid()}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();

        $entry = $results[0];
        $entry->setAttribute('gecos', [$person->getGecos()]);
        $entry->setAttribute('gidNumber', [$person->getGidNumber()]);
        $entry->setAttribute('uidNumber', [$person->getUidNUmber()]);
        $entry->setAttribute('homeDirectory', [$person->getHomeDirectory()]);
        $entryManager->update($entry);
    }

    public function updatePersonEntity(People $person, object $ldap_person): object
    {
        if (!$person) {
            $person = new People();
        }

        $person->setUid(
            current($ldap_person->getAttributes()["uid"])
        );
        $person->setType("staff");
        if (isset($ldap_person->getAttributes()["gecos"])) {
            $person->setGecos(
                current($ldap_person->getAttributes()["gecos"])
            );
        }
        $person->setUidNumber(
            current($ldap_person->getAttributes()["uidNumber"])
        );
        $person->setGidNumber(
            current($ldap_person->getAttributes()["gidNumber"])
        );
        $person->setHomeDirectory(
            current($ldap_person->getAttributes()["homeDirectory"])
        );
        
        $this->om->persist($person);
        $this->om->flush();
        
        return $person;
    }

    public function findOneByUid(string $uid): ?object
    {
        $query = $this->ldap->query(
            "ou=people,{$_ENV['LDAP_DC']}",
            "(&(ObjectClass=posixAccount)(uid={$uid}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();
        if ($results->count() === 0) {
            return null;
        }

        return current($results->toArray());
    }
}
