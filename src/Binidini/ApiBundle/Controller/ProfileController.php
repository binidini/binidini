<?php

namespace Binidini\ApiBundle\Controller;

use Binidini\CoreBundle\Entity\Review;
use Binidini\CoreBundle\Entity\ReviewRepository;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Form\Type\ProfileFormType;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class ProfileController extends Controller
{
    /**
     * Edit the user
     */
    public function editAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $data = $this->get('request')->request->all();
        $form->bind($data);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            $view = View::create($user, 200);
        } else {
            $view = View::create($form, 400);
        }
        return $this->get('fos_rest.view_handler')->handle($view);
    }

    #region API 2

    public function editUserAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse("Bad requesasdfasdt", 400);
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $data = $this->get('request')->request->all();
        $form->bind($data);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            $view = View::create($user, 200);
        } else {
            $view = View::create($form, 405);
        }
        return $this->get('fos_rest.view_handler')->handle($view);
    }

    public function getReviewsAction(Request $request)
    {
        if (!$this->getUser()) {
            return new JsonResponse("Forbidden", 403);
        }
        $userId = $request->get('user_id', 0);
        if ($userId <= 0) {
            return new JsonResponse("Bad request", 400);
        }
        $em = $this->getDoctrine()->getManager();
        /**
         * @var ReviewRepository $repository
         */
        $repository = $em->getRepository(get_class(new Review()));
        /**
         * @var Review[] $reviews
         */
        $reviews = $repository->findBy(["userTo" => $userId]);
        $result = [];
        foreach ($reviews as $review) {
            $result[] = array(
                'id' => $review->getId(),
                'comment' => $review->getText(),
                'created_at' => $review->getCreatedAt()->format(\DateTime::ISO8601),
                'user_from' => $review->getUser()->getResultWrapper(),
                'rate' => $review->getRating()
            );
        }
        return new JsonResponse($result);
    }

    #endregion
}
