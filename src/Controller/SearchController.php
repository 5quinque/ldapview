<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\People;
use App\Entity\Netgroup;
use App\Entity\Host;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/{query}", name="search")
     */
    public function index(string $query)
    {
        $results = [];

        $people = $this->getDoctrine()->getRepository(People::class)->findByLike($query);
        foreach($people as $person) {
            $results[] = array(
                "name" => $person->getUID(),
                "href" => $this->generateURL("people_show", ["uid" => $person->getUID()]),
                "category" => "people",
            );
        }
        $netgroups = $this->getDoctrine()->getRepository(Netgroup::class)->findByLike($query);
        foreach($netgroups as $netgroup) {
            $results[] = array(
                "name" => $netgroup->getName(),
                "href" => $this->generateURL("netgroup_show", ["name" => $netgroup->getName()]),
                "category" => "netgroup",
            );
        }
        $hosts = $this->getDoctrine()->getRepository(Host::class)->findByLike($query);
        foreach($hosts as $host) {
            $results[] = array(
                "name" => $host->getName(),
                "href" => $this->generateURL("host_show", ["name" => $host->getName()]),
                "category" => "host",
            );
        }

        $json = json_encode($results);
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
