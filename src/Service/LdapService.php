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

    public function findOneByUid(string $uid): ?object
    {
        $query = $this->ldap->query('dc=example,dc=org',
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
