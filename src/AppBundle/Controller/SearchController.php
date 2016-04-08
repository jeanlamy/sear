<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SearchController extends Controller
{
    /**
     *
     * @return Response
     * @Route("/search/index")
     */
    public function indexAction()
    {
        return $this->render('search/index.twig');
        
    }


    /**
     *
     * @Route("/search/suggest/{term}")
     */
    public function suggestAction($term)
    {
        
        $es = $this->get('app.elasticsearch');
        $results = $es->getSuggestions($term);
        $response = new Response();
        $response->setContent(json_encode($results));
        $response->header->set('Content-type', 'application/json');
        return $response;
    }

}
