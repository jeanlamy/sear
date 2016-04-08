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

}
