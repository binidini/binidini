<?php

namespace Binidini\CoreBundle\Controller;

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

class ReviewController extends ResourceController
{

    public function createAction(Request $request)
    {
        $resource = $this->createNew();
        $form = $this->getForm($resource);

        if ($form->handleRequest($request)->isValid()) {
            $resource = $this->domainManager->create($resource);

            if ($this->config->isApiRequest()) {
                return $this->handleView($this->view($resource, 201));
            }

            if (null === $resource) {
                return $this->redirectHandler->redirectToIndex();
            }

            return $this->redirectHandler->redirectTo($resource);
        }

        if ($this->config->isApiRequest()) {
            return $this->handleView($this->view($form));
        }
        $errors = $this->getErrorMessages($form);
        /** @var $flashBack FlashBag
         */
        $flashBack = $this->get('session')->getFlashBag();
        foreach ($errors as $error) {
            foreach($error as $message){
                $flashBack->add('danger', $message);
            }
        }
        return $this->redirectHandler->redirectTo($resource);
    }

    private function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        return $errors;
    }

}