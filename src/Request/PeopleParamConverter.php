<?php
namespace App\Request;

use App\Repository\PeopleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\People;
use App\Service\LdapService;

class PeopleParamConverter implements ParamConverterInterface
{
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
   

    public function __construct(PeopleRepository $peopleRepository, ObjectManager $objectManager)
    {
        $this->peopleRepository = $peopleRepository;
        $this->om = $objectManager;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $ldapService = new LdapService();

        $uid = $request->attributes->get('uid');
        $person = $this->peopleRepository->findOneBy(array('uid' => $uid));
        
        if (!$person) {
            $ldap_person = $ldapService->findOneByUid($uid);

            if (is_null($ldap_person)) {
                throw new NotFoundHttpException();
            }

            $person = new People();

            $person->setUid($uid);
            $person->setType("staff");
            $person->setGecos(
                current($ldap_person->getAttributes()["gecos"])
            );
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
        }

        $param = $configuration->getName();
        
        $request->attributes->set($param, $person);

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        return "App\Entity\People" === $configuration->getClass();
    }
}