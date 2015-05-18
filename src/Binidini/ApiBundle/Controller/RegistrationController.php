<?php

namespace Binidini\ApiBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{

    /**
     * @param Request $request
     * @return null|Response|Response
     */
    public function registerAction(Request $request)
    {
        /** @var $formFactory FactoryInterface */
        $formFactory = $this->container->get('fos_user.registration.form.factory');
        /** @var $userManager UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        $form = $formFactory->createForm();
        $form->setData($user);
        $data = $this->get('request')->request->all();
        if ('POST' === $request->getMethod()) {
            $form->bind($data);
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                $userManager->updateUser($user);
                $response = new JsonResponse(["success" => true]);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
                return $response;
            }
        }
        $view = View::create($form, 400);
        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
