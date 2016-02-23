<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\User;
use Binidini\CoreBundle\Service\NotificationService;
use Binidini\CoreBundle\Service\SecurityService;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\UserManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserController extends Controller
{

    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        /** @var EncoderFactoryInterface $factory */
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $currentPassword = $request->get('current_password');
        $newPassword = $request->get('new_password');
        $newPasswordConfirm = $request->get('new_password_confirm');

        if ($encoder->isPasswordValid($user->getPassword(), $currentPassword, $user->getSalt())) {
            if ($newPassword == $newPasswordConfirm) {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->get('fos_user.user_manager');
                $user->setPlainPassword($newPassword);
                $userManager->updateUser($user);

                return new JsonResponse(['status' => 1]);
            } else {
                $error = 'Новый пароль и его подтверждение не совпадают';
            }
        } else {
            $error = 'Старый пароль введен неверно';
        }

        return new JsonResponse(['status' => 0, 'error' => $error]);
    }

    public function resetPasswordAction(Request $request)
    {
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');
        $username = $request->get('username');
        if (!$username) {
            return new Response(
                $this->get('translator')->trans('resetting.password.max.nousername'), 400
            );
        }
        /** @var $user User */
        $user = $userManager->findUserByUsernameOrEmail($username);
        if (!$user) {
            return new Response(
                $this->get('translator')->trans('resetting.password.max.nousername'), 404
            );
        }
        /** @var \Memcached $memcached */
        $memcached = $this->get('memcache.default');

        $attemptKey = User::PASSWORD_RECOVER_ATTEMPT_PREFIX . $username;
        $attemptCounter = $memcached->get($attemptKey) ?: 0;

        if ($attemptCounter && $attemptCounter > User::PASSWORD_RECOVER_ATTEMPTS) {
            return new Response(
                $this->get('translator')->trans('resetting.password.max.attempts'), 403
            );
        }

        if ($attemptCounter) {
            $memcached->set($attemptKey, $attemptCounter + 1);
        } else {
            $memcached->set($attemptKey, 1, User::PASSWORD_RECOVER_ATTEMPTS_TTL);
        }

        $user->setRecoverSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        $plainPassword = SecurityService::generatePassword();
        /** @var EncoderFactoryInterface $encoderFactory */
        $encoderFactory = $this->get("security.encoder_factory");
        $encoder = $encoderFactory->getEncoder($user);
        $user->setRecoverPassword($encoder->encodePassword($plainPassword, $user->getRecoverSalt()));
        $userManager->updateUser($user);
        $memcached->set(User::PASSWORD_RECOVER_PREFIX . $username, 1, User::PASSWORD_RECOVER_TTL);
        /** @var NotificationService $notificationService */
        $notificationService = $this->get('binidini.notification.service');
        $notificationService->setRecoverPasswordSms($username, $plainPassword);
        /** @var $flashBack FlashBag */
        $flashBag = $this->get('session')->getFlashBag();

        $flashBag->add('success', $this->get('translator')->trans('resetting.password.success.send'));

        return new JsonResponse(['redirect' => $this->generateUrl('fos_user_security_login')]);
    }

    public function lockAction(Request $request)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        $userId = $request->get('id');
        if ($userId) {
            /** @var User $user */
            $user = $userManager->findUserBy(['id' => $userId]);
            if (!$user) {
                throw new NotFoundHttpException('Пользователь не найден');
            }
            if (!$user->isLocked()) {
                $user->setLocked(true);
                $userManager->updateUser($user);
            }
        } else {
            throw new InvalidParameterException('Id пользователя не передано');
        }

        return new RedirectResponse($this->generateUrl('binidini_admin_user_show', ['id' => $user->getId()]));
    }

    public function unlockAction(Request $request)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        $userId = $request->get('id');
        if ($userId) {
            /** @var User $user */
            $user = $userManager->findUserBy(['id' => $userId]);
            if (!$user) {
                throw new NotFoundHttpException('Пользователь не найден');
            }
            if ($user->isLocked()) {
                $user->setLocked(false);
                $userManager->updateUser($user);
            }
        } else {
            throw new InvalidParameterException('Id пользователя не передано');
        }
        return new RedirectResponse($this->generateUrl('binidini_admin_user_show', ['id' => $user->getId()]));
    }


    //API V2

    public function getUserAction(Request $request)
    {
        /** @var $user User */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse("Forbidden", 403);
        }
        $id = $request->get('id');
        if ($id) {
            /** @var $userManager UserManager */
            $userManager = $this->container->get('fos_user.user_manager');
            $user = $userManager->findUserBy(['id' => $id]);
            if (!$user) {
                return new JsonResponse("Bad request", 400);
            }
        } else {
            if (!$user) {
                return new JsonResponse("Bad request", 400);
            }
        }
        $result = [
            'user_id' => $user->getId(),
            'firstname' => $user->getFirstName(),
            'lastname' => $user->getLastName(),
            'email' => $user->getEmail(),
            'phone' => $user->getUsername(),
            'about_me' => $user->getAboutMe(),
            'img_path' => $user->getImgPath()
        ];
        return new JsonResponse($result);
    }

    public function resetUserPasswordAction(Request $request)
    {
        if ($this->getUser()) {
            return new JsonResponse("Forbidden", 403);
        }
        /** @var UserManager $userManager */
        $userManager = $this->get('fos_user.user_manager');
        $username = $request->get('username');
        if (!$username) {
            return new JsonResponse("Bad request", 400);
        }
        /** @var $user User */
        $user = $userManager->findUserByUsername($username);
        if (!$user) {
            return new JsonResponse(['code' => 1, 'message' => 'Пользователь не найден']);
        }
        /** @var \Memcached $memcached */
        $memcached = $this->get('memcache.default');
        $attemptKey = User::PASSWORD_RECOVER_ATTEMPT_PREFIX . $username;
        $attemptCounter = $memcached->get($attemptKey) ?: 0;
        if ($attemptCounter && $attemptCounter > User::PASSWORD_RECOVER_ATTEMPTS) {
            return new JsonResponse([
                'code' => 2,
                'message' => $this->get('translator')->trans('resetting.password.max.attempts')
            ]);
        }

        if ($attemptCounter) {
            $memcached->set($attemptKey, $attemptCounter + 1);
        } else {
            $memcached->set($attemptKey, 1, User::PASSWORD_RECOVER_ATTEMPTS_TTL);
        }

        $user->setRecoverSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        $plainPassword = SecurityService::generatePassword();
        /** @var EncoderFactoryInterface $encoderFactory */
        $encoderFactory = $this->get("security.encoder_factory");
        $encoder = $encoderFactory->getEncoder($user);
        $user->setRecoverPassword($encoder->encodePassword($plainPassword, $user->getRecoverSalt()));
        $userManager->updateUser($user);
        $memcached->set(User::PASSWORD_RECOVER_PREFIX . $username, 1, User::PASSWORD_RECOVER_TTL);
        /** @var NotificationService $notificationService */
        $notificationService = $this->get('binidini.notification.service');
        $notificationService->setRecoverPasswordSms($username, $plainPassword);

        return new JsonResponse(['code' => 3, 'message' => 'Новый пароль отправлен по SMS']);
    }

    /**
     * @param Request $request
     * @return null|Response|Response
     */
    public function registerUserAction(Request $request)
    {
        if ($this->getUser()) {
            return new JsonResponse("Forbidden", 403);
        }
        $username = $request->get('username');
        if (!$username) {
            return new JsonResponse("Bad request", 400);
        }
        /** @var $userManager UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);
        if ($user) {
            return new JsonResponse(['code' => 1, 'message' => 'Пользователь уже существует']);
        }
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
                return new JsonResponse(['code' => 2, 'message' => 'Пользователь создан, пароль отправлен по SMS']);
            }
        }
        return new JsonResponse("Bad requests", 400);



        /** @var $formFactory FactoryInterface */
        $formFactory = $this->container->get('fos_user.registration.form.factory');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        $form = $formFactory->createForm();
        $form->setData($user);
        $data = $this->get('request')->request->all();
        $form->bind($data);
        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
            $userManager->updateUser($user);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
            return new JsonResponse(['code' => 2, 'message' => 'Пользователь создан, пароль отправлен по SMS']);
        }
        return new JsonResponse("Bad requests", 400);

//        if ($this->getUser()) {
//            return new JsonResponse("Forbidden", 403);
//        }
//        $username = $request->get('username');
//        if (!$username) {
//            return new JsonResponse("Bad request", 400);
//        }
//        /** @var $userManager UserManagerInterface */
//        $userManager = $this->container->get('fos_user.user_manager');
//        $user = $userManager->findUserByUsername($username);
//        if ($user) {
//            return new JsonResponse(['code' => 1, 'message' => 'Пользователь уже существует']);
//        }
//        /** @var $formFactory FactoryInterface */
//        $formFactory = $this->container->get('fos_user.registration.form.factory');
//        $form = $formFactory->createForm();
//        $form->setData($user);
//        $data = $this->get('request')->request->all();
//        if ('POST' === $request->getMethod()) {
//            $form->bind($data);
//        }
//        /** @var $user User */
//        $user = $userManager->createUser();
//        $user->setEnabled(true);
//        $user->setEmail('');
//        $plainPwd = rand(100000, 999999);
//        $user->setPlainPassword("{$plainPwd}");
//        $userManager->updateUser($user);
//        $msg = array('mobile' => $user->getUsername(), 'sms' => "Ваш пароль: {$plainPwd}");
//        /** @var $rabbitMqProducer Producer */
//        $rabbitMqProducer = $this->get("old_sound_rabbit_mq.binidini_sms_producer");
//        $rabbitMqProducer->publish(serialize($msg));
//        return new JsonResponse(['code' => 2, 'message' => 'Пользователь создан, пароль отправлен по SMS']);
    }

}
