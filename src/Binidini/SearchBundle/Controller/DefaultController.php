<?php

namespace Binidini\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BinidiniSearchBundle:Default:index.html.twig', array('name' => $name));

    }
}
