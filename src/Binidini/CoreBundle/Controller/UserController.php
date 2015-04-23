<?php

namespace Binidini\CoreBundle\Controller;

use Binidini\CoreBundle\Entity\User;
use Binidini\CoreBundle\Service\NotificationService;
use Binidini\CoreBundle\Service\SecurityService;
use FOS\UserBundle\Model\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder;
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
            if ($newPassword ==  $newPasswordConfirm) {
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
                $this->get('translator')->trans('resetting.password.max.nousername'), 400
            );
        }
        /** @var \Memcached $memcached */
        $memcached = $this->get('memcache.default');

        $attemptKey = User::PASSWORD_RECOVER_ATTEMPT_PREFIX.$username;
        $attemptCounter = $memcached->get($attemptKey) ?: 0;

        if ($attemptCounter && $attemptCounter > User::PASSWORD_RECOVER_ATTEMPTS) {
            return new Response(
                $this->get('translator')->trans('resetting.password.max.attempts'), 400
            );
        }

        if ($attemptCounter) {
            $memcached->set($attemptKey, $attemptCounter + 1);
        } else {
            $memcached->set($attemptKey, 1, User::PASSWORD_RECOVER_ATTEMPTS_TTL);
        }

        $user->setRecoverSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        $plainPassword = SecurityService::generatePassword();
        $encoder = new Pbkdf2PasswordEncoder();
        $user->setRecoverPassword($encoder->encodePassword($plainPassword, $user->getRecoverSalt()));
        $userManager->updateUser($user);
        $memcached->set(User::PASSWORD_RECOVER_PREFIX.$username, 1, User::PASSWORD_RECOVER_TTL);
        /** @var NotificationService $notificationService */
        $notificationService = $this->get('binidini.notification.service');
        $notificationService->setRecoverPasswordSms($username, $plainPassword);
        /** @var $flashBack FlashBag */
        $flashBag = $this->get('session')->getFlashBag();

        $flashBag->add('success',$this->get('translator')->trans('resetting.password.success.send'));

        return new JsonResponse(['redirect' => $this->generateUrl('fos_user_security_login')]);
    }
}