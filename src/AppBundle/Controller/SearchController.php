<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

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
        
        $s = array();
        foreach($results['suggestions'][0]['options'] as $row) {
            $s[] = [ 'value' => $row['text']];
        }
        return new JsonResponse($s);
    }

}
