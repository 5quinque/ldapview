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

    public function loadAll()
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

    public function createNetgroupsIfNotExists(array $netgroups, Host $host)
    {
        foreach ($netgroups as $netgroup) {
            $this->createNetgroupIfNotExists($netgroup, $host);
        }
    }

    public function readAll()
    {
        $directory = $_ENV['ACCESS_DIR'];

        $files = scandir($directory);

        $hosts = [];
        $netgroups = [];

        foreach ($files as $hostname) {
            if (
                in_array($hostname, array('.', '..'))
                || !is_dir("$directory/$hostname")
            )
                continue;

            $accessFile = $this->getAccessFile("$directory/$hostname");

            $hosts[] = $hostname;
            $host = $this->createHostIfNotExists($hostname);
            $netgroups = $this->getNetgroups($accessFile);

            $this->createNetgroupsIfNotExists($netgroups, $host);
        }
        return $hosts;
    }

    private function getAccessFile($hostDirectory)
    {
        if (is_file("$hostDirectory/etc/security/access.conf")) {
            return "$hostDirectory/etc/security/access.conf";
        }
        if (is_file("$hostDirectory/etc/passwd")) {
            return "$hostDirectory/etc/passwd";
        }
        return null;
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
        // RegEx for access.conf (RedHat)
        preg_match('/^\+ : @(.+) : ALL$/', $accessline, $matches);
        if (!empty($matches)) {
            return $matches[1];
        }
        // RegEx for passwd (Solaris)
        // Possibly change (.+?) for ([\w-]+)
        preg_match('/^\+@(.+?):.+$/', $accessline, $matches);
        if (!empty($matches)) {
            return $matches[1];
        }

        return false;
    }
}
