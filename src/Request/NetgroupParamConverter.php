<?php
namespace App\Request;

use App\Repository\NetgroupRepository;
use App\Repository\PeopleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Netgroup;
use App\Entity\People;
use App\Service\LdapService;

class NetgroupParamConverter implements ParamConverterInterface
{
    private $netgroupRepository;

    private $peopleRepository;

    /**                                                                              
     * The parameter name.                                                           
     *                                                                               
     * @var string                                                                   
     */                                                                              
    private $name;                                                                   
                                                                                     
    /**                                                                              
     * The parameter class.                                                          
     *                                                                               
     * @var string                                                                   
     */                                                                              
    private $class;                                                                  
                                                                                     
    /**                                                                              
     * An array of options.                                                          
     *                                                                               
     * @var array                                                                    
     */                                                                              
    private $options = [];                                                           
   

    public function __construct(NetgroupRepository $netgroupRepository, PeopleRepository $peopleRepository, ObjectManager $objectManager)
    {
        $this->netgroupRepository = $netgroupRepository;
        $this->peopleRepository = $peopleRepository;
        $this->om = $objectManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $ldapService = new LdapService();

        $name = $request->attributes->get('name');
        $netgroup = $this->netgroupRepository->findOneBy(array('name' => $name));
        $ldap_netgroup = $ldapService->findOneByNetgroup($name);

        if (is_null($ldap_netgroup)) {
            // [todo] if $person exists, remove entity?
            throw new NotFoundHttpException("Netgroup not found in LDAP");
        }
        
        if (!$netgroup) {
            $netgroup = new People();
        }
        
        // Add these users to netgroup
        foreach ($ldap_netgroup->getAttributes()["nisNetgroupTriple"] as $nis) {
            //dump($nis);
            preg_match('/^\(,(.+),.+\)$/', $nis, $matches);
            if (empty($matches)) {
                continue;
            }
            dump($matches[1]);
            $person = $this->peopleRepository->findOneBy(array('uid' => $matches[1]));
            //dump($person->toArray());
            
            if (!is_null($person)) {
                // If person is null, try searching them in ldap and create new entity?
                $netgroup->addPerson($person);
            }
        }

        $netgroup->setDescription(
            current($ldap_netgroup->getAttributes()["description"])
        );
        
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