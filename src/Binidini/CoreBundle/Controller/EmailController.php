<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\User;
use Binidini\CoreBundle\Service\NotificationService;
use Doctrine\Common\Util\Debug;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailController extends Controller
{

    public function sendConfirmEmailAction()
    {
        /** @var $user \Binidini\CoreBundle\Entity\User */
        $user = $this->getUser();
        $status = 0;
        if (!$user->getEmailVerified() && $user->getEmail()) {
            $user->setConfirmationToken($user->getConfirmationToken());
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            /** @var NotificationService $notificationService */
            $notificationService = $this->get('binidini.notification.service');
            $notificationService->sendConfirmationEmail($user);
            $status = 1;
        }

        return JsonResponse::create(['success' => $status]);
    }

    public function confirmAction($token)
    {
        /** @var $userManager UserManager */
        $userManager = $this->get('fos_user.user_manager');
        /** @var User $user */
        $user = $userManager->findUserByConfirmationToken([$token]);
        if ($user && !$user->getEmailVerified()){
            $user->setEmailVerified(true);
            $userManager->updateUser($user);
            /** @var $flashBack FlashBag */
            $flashBag = $this->get('session')->getFlashBag();
            $flashBag->add('alert','Ваша почта подтверждена');
            return RedirectResponse::create($this->generateUrl('fos_user_profile_show'));
        } else {
            throw new NotFoundHttpException("Пользователь не найден");
        }
    }

}