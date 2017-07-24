<?php

namespace MyBenchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
    	var_dump(get_class_methods($this->get('doctrine.orm.default_entity_manager'))); die();
        return $this->render('MyBenchBundle:Default:index.html.twig');
    }
}
