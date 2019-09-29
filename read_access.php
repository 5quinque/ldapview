<?php


namespace App\Test;

//use App\Repository\NetgroupRepository;
//use Symfony\Component\Workflow\Registry;

//use Doctrine\ORM\EntityManagerInterface;
use App\Controller\NetgroupController;
//use App\Entity\Netgroup;
//use Doctrine\Bundle\FixturesBundle\Fixture;
//use Doctrine\Common\Persistence\ObjectManager;

require __DIR__.'/vendor/autoload.php';

//$x = new NetgroupObjectManager();

//$x->load();

//$om = new ObjectManager();
//load($om);








//$reg = new \Doctrine\Bundle\DoctrineBundle\Registry();
//$ng_rep = new NetgroupRepository($reg);


//
$ng_controller = new NetgroupController();
//
//
$ng_controller->newFromScript("test nergroup");



function readAll()
{
    $directory = "/home/ryan/dev/ldapview/accessfiles";

    $files = scandir($directory);

    foreach ($files as $f) {
        if (in_array($f, array('.', '..'))
            || !is_dir($f)
            || !is_file("$f/etc/security/access.conf"))
            continue;

        $netgroups = getNetgroups("$f/etc/security/access.conf");
    }
}

function getNetgroups($accessFile)
{
    $netgroups = [];
    $access_handle = fopen($accessFile, "r");
    while ($line = fgets($access_handle)) {
        $netgroup = parseNetgroup($line);
        if ($netgroup) {
            $netgroups[] = $netgroup;
        }
    }
    fclose($access_handle);

    return $netgroups;
}

function parseNetgroup($accessline)
{
    preg_match('/^\+ : @(.+) : ALL$/', $accessline, $matches);
    if (!empty($matches)) {
        return $matches[1];
    }
    return false;
}
