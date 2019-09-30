<?php

namespace App\Service;

use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Netgroup;
use App\Entity\Host;
use App\Repository\HostRepository;
use App\Repository\NetgroupRepository;

class LoadNetgroups
{
    private $om;
    private $netgroupRepository;
    private $hostRepository;

    public function __construct(ObjectManager $objectManager, NetgroupRepository $netgroupRepository, HostRepository $hostRepository)
    {
        $this->om = $objectManager;
        $this->netgroupRepository = $netgroupRepository;
        $this->hostRepository = $hostRepository;
    }

    public function test()
    {
        $messages = [];

        $hosts = $this->readAll();
        foreach ($hosts as $h) {
            $messages[] = $h;
        }

        return $messages;
    }

    public function printDebug(array $debug)
    {
        foreach ($debug as $d) {
            print "$d";
        }
    }

    public function createHostIfNotExists($hostname): Host
    {
        $debug = ["Hostname: $hostname\n"];

        $host = $this->hostRepository->findOneBy(array('name' => $hostname));
        if (empty($host)) {
            $debug[] = "Creating new host entity\n";
            $host = new Host();
            $host->setName($hostname);
            $this->om->persist($host);
            $this->om->flush();
        }

        $this->printDebug($debug);

        return $host;
    }

    public function createNetgroupIfNotExists(string $netgroupName, Host $host): Netgroup
    {
        $debug = ["Netgroup: $netgroupName\n"];

        $netgroup = $this->netgroupRepository->findOneBy(array('name' => $netgroupName));
        if (empty($netgroup)) {
            $debug[] = "Creating new netgroup entity\n";
            $netgroup = new Netgroup();
            $netgroup->setName($netgroupName);
        }

        $debug[] = "Adding {$host->getName()} to $netgroupName\n";
        $netgroup->addHost($host);
        $this->om->persist($netgroup);
        $this->om->flush();

        $this->printDebug($debug);

        return $netgroup;
    }

    public function readAll()
    {
        $directory = "/home/ryan/dev/ldapview/accessfiles";

        $files = scandir($directory);

        $hosts = [];
        $netgroups = [];

        foreach ($files as $f) {
            if (in_array($f, array('.', '..'))
                || !is_dir("$directory/$f")
                || !is_file("$directory/$f/etc/security/access.conf"))
                continue;

            $hosts[] = $f;
            $host = $this->createHostIfNotExists($f);

            $netgroups = $this->getNetgroups("$directory/$f/etc/security/access.conf");

            foreach($netgroups as $netgroup) {
                $this->createNetgroupIfNotExists($netgroup, $host);
            }
        }
        return $hosts;
    }

    public function getNetgroups($accessFile)
    {
        $netgroups = [];
        $access_handle = fopen($accessFile, "r");
        while ($line = fgets($access_handle)) {
            $netgroup = $this->parseNetgroup($line);
            if ($netgroup) {
                $netgroups[] = $netgroup;
            }
        }
        fclose($access_handle);

        return $netgroups;
    }

    public function parseNetgroup($accessline)
    {
        preg_match('/^\+ : @(.+) : ALL$/', $accessline, $matches);
        if (!empty($matches)) {
            return $matches[1];
        }
        return false;
    }

}