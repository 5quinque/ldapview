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
   

    public function __construct(PeopleRepository $peopleRepository, ObjectManager $objectManager, LdapService $ldapService)
    {
        $this->peopleRepository = $peopleRepository;
        $this->om = $objectManager;
        $this->ldapService = $ldapService;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $uid = $request->attributes->get('uid');
        $person = $this->peopleRepository->findOneBy(array('uid' => $uid));
        $ldap_person = $this->ldapService->findOneByUid($uid);

        if (is_null($ldap_person)) {
            // [todo] if $person exists, remove entity?
            throw new NotFoundHttpException("User not found in LDAP");
        }
        
        if (!$person) {
            $person = $this->ldapService->createPersonEntity($ldap_person);
        } else {
            $this->ldapService->updatePersonEntity($person, $ldap_person);
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