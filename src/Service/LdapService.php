<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use App\Entity\People;

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

    public function persist(People $person)
    {
        $entryManager = $this->ldap->getEntryManager();
        $query = $this->ldap->query('ou=people,dc=example,dc=org',
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

    public function findOneByUid(string $uid): ?object
    {
        $query = $this->ldap->query('ou=people,dc=example,dc=org',
            "(&(ObjectClass=posixAccount)(uid={$uid}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();
        if ($results->count() === 0) {
            return null;
        }

        return current($results->toArray());
    }

    public function findOneByNetgroup(string $name): ?object
    {
        $query = $this->ldap->query('ou=netgroup,dc=example,dc=org',
            "(&(structuralObjectClass=nisNetgroup)(cn={$name}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();
        if ($results->count() === 0) {
            return null;
        }

        return current($results->toArray());
    }

    public function findAll() {
        $query = $this->ldap->query('ou=people,dc=example,dc=org', 'objectClass=posixAccount',
            [
                "pageSize" => 10,
            ]
        );
        $results = $query->execute();
        dump($results->toArray());

        return $results;
    }
}
