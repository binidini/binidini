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


use Binidini\CoreBundle\Entity\Message;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Bid controller
 *
 * @author Denis Manilo <denis@binidini.com>
 */
class MessageController extends ResourceController
{
    protected $stateMachineGraph = Message::GRAPH;

    public function updateStateAction(Request $request, $transition, $graph = null)
    {
        $resource = $this->findOr404($request);


        if (null === $graph) {

            $graph = $this->stateMachineGraph;
        }

        $stateMachine = $this->get('sm.factory')->get($resource, $graph);
        if (!$stateMachine->can($transition)) {
            throw new NotFoundHttpException(sprintf(
                'The requested transition %s cannot be applied on the given %s with graph %s.',
                $transition,
                $this->config->getResourceName(),
                $graph
            ));
        }

        $stateMachine->apply($transition);

        $this->domainManager->update($resource);

        if ($this->config->isApiRequest()) {
            if ($resource instanceof ResourceEvent) {
                throw new HttpException($resource->getErrorCode(), $resource->getMessage());
            }

            return $this->handleView($this->view($resource, 204));
        }

        return $this->redirectHandler->redirectToRoute($this->config->getRedirectRoute('index'), $this->config->getRedirectParameters());
    }
}