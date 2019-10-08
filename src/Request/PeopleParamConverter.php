<?php

namespace App\Request;

use App\Repository\PeopleRepository;
use App\Repository\NetgroupRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Persistence\ObjectManager;
use App\Service\LdapPeopleService;

class PeopleParamConverter implements ParamConverterInterface
{
    private $peopleRepository;
    private $netgroupRepository;

    public function __construct(PeopleRepository $peopleRepository, NetgroupRepository $netgroupRepository, ObjectManager $objectManager, LdapPeopleService $ldapPeopleService)
    {
        $this->peopleRepository = $peopleRepository;
        $this->netgroupRepository = $netgroupRepository;
        $this->om = $objectManager;
        $this->ldapPeopleService = $ldapPeopleService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $uid = $request->attributes->get('uid');
        $person = $this->peopleRepository->findOneBy(array('uid' => $uid));
        $ldap_person = $this->ldapPeopleService->findOneByUid($uid);

        if (is_null($ldap_person)) {
            // [todo] if $person exists, remove entity?

            throw new NotFoundHttpException("User not found in LDAP");
        }

        if (!$person) {
            $person = $this->ldapPeopleService->createPersonEntity($ldap_person);
        } else {
            $this->ldapPeopleService->updatePersonEntity($person, $ldap_person);
        }

        // Find netgroups for user
        // $netgroup = $this->findNetgroups("unixadms");
        // dump($netgroup);
        // dump($netgroup->getPeople());

        $param = $configuration->getName();

        $request->attributes->set($param, $person);

        return true;
    }

    public function findNetgroups(string $name)
    {
        $netgroup = $this->netgroupRepository->findOneBy(array('name' => $name));

        return $netgroup;
    }

    public function supports(ParamConverter $configuration)
    {
        return "App\Entity\People" === $configuration->getClass();
    }
}
