<?php

namespace App\Service;

use Symfony\Component\Ldap\Ldap;
use Symfony\Component\Ldap\Entry;
use Doctrine\Common\Persistence\ObjectManager;
use App\Service\LdapService;
use App\Service\LdapNetgroupService;
use App\Service\LdapPeopleService;
use App\Repository\SudoRepository;
use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;
use App\Repository\HostRepository;
use App\Entity\Sudo;
use App\Entity\SudoCommand;
use App\Entity\Netgroup;
use App\Entity\People;

class LdapSudoService
{
    private $ldap;

    public function __construct(
        ObjectManager $objectManager,
        NetgroupRepository $netgroupRepository,
        PeopleRepository $peopleRepository,
        HostRepository $hostRepository,
        LdapPeopleService $ldapPeopleService,
        LdapNetgroupService $ldapNetgroupService
    ) {
        $this->ldap = Ldap::create('ext_ldap', [
            'host' => $_ENV['LDAP_HOST'],
            'encryption' => 'none',
        ]);

        $this->ldap->bind($_ENV['LDAP_USER'], $_ENV['LDAP_PASS']);

        $this->om = $objectManager;
        $this->netgroupRepository = $netgroupRepository;
        $this->peopleRepository = $peopleRepository;
        $this->hostRepository = $hostRepository;
        $this->ldapPeopleService = $ldapPeopleService;
        $this->ldapNetgroupService = $ldapNetgroupService;
    }

    public function createSudoEntity(object $ldap_sudo): object
    {
        $sudo = new Sudo();

        return $this->updateSudoEntity($sudo, $ldap_sudo);
    }

    public function updateSudoEntity($sudo, object $ldap_sudo): object
    {
        if (!$sudo) {
            $sudo = new Sudo();
        }

        $sudo->setName(
            current($ldap_sudo->getAttributes()["cn"])
        );

        if (isset($ldap_sudo->getAttributes()["description"])) {
            $sudo->setDescription(
                current($ldap_sudo->getAttributes()["description"])
            );
        }

        // sudoUser can also includes netgroups. Which are prefixed with `+`
        if (isset($ldap_sudo->getAttributes()["sudoUser"])) {
            $this->addUsers($sudo, $ldap_sudo->getAttributes()["sudoUser"]);
        }

        
        if (isset($ldap_sudo->getAttributes()["sudoHost"])) {
            $this->addHosts($sudo, $ldap_sudo->getAttributes()["sudoHost"]);
        }

        if (isset($ldap_sudo->getAttributes()["sudoCommand"])) {
            $this->addCommands($sudo, $ldap_sudo->getAttributes()["sudoCommand"]);
        }

        $this->om->persist($sudo);
        $this->om->flush();

        return $sudo;
    }

    public function findOneBySudo(string $name): ?object
    {
        $query = $this->ldap->query(
            "ou=SUDOers,{$_ENV['LDAP_DC']}",
            "(&(structuralObjectClass=sudoRole)(cn={$name}))",
            ["maxItems" => 1]
        );

        $results = $query->execute();
        if ($results->count() === 0) {
            return null;
        }

        return current($results->toArray());
    }

    public function addCommands(Sudo $sudo, array $commands)
    {
        foreach($commands as $command) {
            // echo "Command: " . $command . "\n";
            $commandEntity = new SudoCommand();
            $commandEntity->setCommand($command);
            $sudo->addSudoCommand($commandEntity);

            $this->om->persist($commandEntity);
        }
    }

    public function addHosts(Sudo $sudo, array $hosts)
    {
        foreach($hosts as $host) {
            // echo "Host: " . $host . "\n";
            $host = $this->hostRepository->findOneBy(array('name' => $host));

            // If user isn't in LDAP, move on
            if (is_null($host)) {
                continue;
            }

            $sudo->addHost($host);
        }
    }

    public function addUsers(Sudo $sudo, array $people)
    {
        foreach ($people as $uid) {
            // echo $uid . "\n";
            if (preg_match('/^\+/', $uid)) {
                $uid = trim($uid, '+');

                // Get the netgroup entity
                $netgroup = $this->netgroupRepository->findOneBy(array('name' => $uid));
                $ldap_netgroup = $this->ldapNetgroupService->findOneByNetgroup($uid);

                // If netgroup isn't in LDAP, move on / [TODO] Can't find netgroup in ldap..?
                if (is_null($ldap_netgroup) || is_null($netgroup)) {
                    continue;
                }

                $sudo->addNetgroup($netgroup);
            } else {
                // Get the person entity
                $person = $this->peopleRepository->findOneBy(array('uid' => $uid));
                $ldap_person = $this->ldapPeopleService->findOneByUid($uid);

                // If user isn't in LDAP, move on
                if (is_null($ldap_person) || is_null($person)) {
                    continue;
                }

                $sudo->addUser($person);
            }
        }
    }
}
