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

        $es      = $this->get('app.elasticsearch');
        $results = $es->getSuggestions($term);
        
        $s       = array();
        foreach ($results['hits']['hits'] as $row) {
            $s[] = $row['_source']['product_name'];
        }
        $s = array_unique($s);
        $res = array();
        foreach($s as $value) {
            $res[] = ['value' => $value];
        }
        return new JsonResponse($res);
    }
}