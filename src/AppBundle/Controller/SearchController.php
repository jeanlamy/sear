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
     * @Route("/search/index", name="search_index")
     */
    public function indexAction()
    {
        return $this->render('search/index.html.twig');
    }

    /**
     *
     * @Route("/search/suggest/{term}", name="search_suggest")
     */
    public function suggestAction($term)
    {

        $es      = $this->get('app.elasticsearch');
        $results = $es->suggest($term);
        
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

    /**
     * Get search results and aggregations using search term and optionaly
     * filters
     *
     *
     * @param type $term search term
     * @param type $filters optional filters
     * @Route("/search/results/{term}/{filters}", defaults={"filters" = ""}, name="search_results")
     */
    public function resultsAction($term, $filters)
    {
        $es      = $this->get('app.elasticsearch');
        $from = 10;
        $size = 20;

        $filters = explode(',',$filters);
        
        $results = $es->search($term, (array)$filters, $from, $size);
        
        

        return $this->render('search/results.html.twig', array(
            'results' => $results,
            'term' => $term,
            'filters' => $filters
            ));
    }
}