<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\People;
use App\Entity\Netgroup;
use App\Entity\Host;
use App\Entity\Sudo;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/{query}", name="search")
     */
    public function index(string $query = null)
    {
        if (is_null($query)) {
            $response = new Response();
            $response->setContent('{}');
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $results = [];

        // [todo] be able to search on gecos field
        $people = $this->getDoctrine()->getRepository(People::class)->findByLike($query);
        $results = $this->addToResults($results, $people, "people");
        
        $netgroups = $this->getDoctrine()->getRepository(Netgroup::class)->findByLike($query);
        $results = $this->addToResults($results, $netgroups, "netgroup");

        $hosts = $this->getDoctrine()->getRepository(Host::class)->findByLike($query);
        $results = $this->addToResults($results, $hosts, "host");

        $sudoGroups = $this->getDoctrine()->getRepository(Sudo::class)->findByLike($query);
        $results = $this->addToResults($results, $sudoGroups, "sudo");

        $json = json_encode($results);
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    private function addToResults(array $results, array $con_results, string $controller): array
    {
        foreach ($con_results as $result) {
            if ($controller == "people") {
                $name = $result->getUID();
                $href = $this->generateURL("{$controller}_show", ["uid" => $name]);
            } else {
                $name = $result->getName();
                $href = $this->generateURL("{$controller}_show", ["name" => $name]);
            }
            $results[] = array(
                "name" => $name,
                "href" => $href,
                "category" => $controller,
            );
        }
        return $results;
    }
}
