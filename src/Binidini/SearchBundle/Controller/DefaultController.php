<?php

namespace Binidini\SearchBundle\Controller;

use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        /**
         * @var Swift_Mailer $mailer
         */

        $mailer = $this->get('mailer');

        $msg = $mailer->createMessage()
            ->setSubject('subject')
            ->setFrom('info@tytymyty.ru')
            ->setTo('manilo@mail.ru')
            ->setBody('body', 'text/html');

        $res = $mailer->send($msg);

        var_dump($res);

        return $this->render('BinidiniSearchBundle:Default:index.html.twig', array('name' => $name));

    }
}
