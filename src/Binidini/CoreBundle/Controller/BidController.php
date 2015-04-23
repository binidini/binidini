<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\CoreBundle\Controller;


use Binidini\CoreBundle\Entity\Bid;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Bid controller
 *
 * @author Denis Manilo <denis@binidini.com>
 */
class BidController extends ResourceController
{
    protected $stateMachineGraph = Bid::GRAPH;

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
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
        foreach ($errors as $error) {
            foreach ($error as $message) {
                $this->flashHelper->setFlash('danger', 'create.error', ['%error%' => $message]);
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