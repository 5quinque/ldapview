<?php
namespace App\Request;

use App\Repository\NetgroupRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Netgroup;
use App\Entity\People;
use App\Service\LdapNetgroupService;

class NetgroupParamConverter implements ParamConverterInterface
{
    private $netgroupRepository;
    
    public function __construct(
        NetgroupRepository $netgroupRepository,
        ObjectManager $objectManager,
        LdapNetgroupService $ldapNetgroupService
    ) {
        $this->netgroupRepository = $netgroupRepository;
        $this->om = $objectManager;
        $this->ldapNetgroupService = $ldapNetgroupService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $request->attributes->get('name');
        $netgroup = $this->netgroupRepository->findOneBy(array('name' => $name));
        $ldap_netgroup = $this->ldapNetgroupService->findOneByNetgroup($name);

        if (is_null($ldap_netgroup)) {
            // [todo] if $netgroup exists, remove entity?
            //throw new NotFoundHttpException("Netgroup not found in LDAP");
        } else {
	    if (isset($ldap_netgroup->getAttributes()["nisNetgroupTriple"])) {
                $this->ldapNetgroupService->addUsers($netgroup, $ldap_netgroup->getAttributes()["nisNetgroupTriple"]);
            }
        
            if (isset($ldap_netgroup->getAttributes()["description"])) {
                $netgroup->setDescription(
                    current($ldap_netgroup->getAttributes()["description"])
                );
        }
        
	}
        
        if (!$netgroup) {
            $netgroup = new Netgroup();
        }

       
        $this->om->persist($netgroup);
        $this->om->flush();

        $param = $configuration->getName();
        
        $request->attributes->set($param, $netgroup);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return "App\Entity\Netgroup" === $configuration->getClass();
    }
}
