<?php

namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Gedmo\Exception\UploadableException;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class EditUserProfileListener implements EventSubscriberInterface
{

    private $uploadableManager;
    private $doctrineManager;
    private $email;
    private $security;
    private $route;
    private $rootDir;

    public function __construct(UploadableManager $uploadableManager, EntityManager $doctrine, SecurityContextInterface $security, RouterInterface $route, $rootDir)
    {
        $this->uploadableManager = $uploadableManager;
        $this->doctrineManager = $doctrine;
        $this->security = $security;
        $this->route = $route;
        $this->rootDir = $rootDir;
    }

    public static function getSubscribedEvents()
    {
        return [];
    }

    public function onFosuserProfileEditInitialize(GetResponseUserEvent $event)
    {
        $this->email = $this->security->getToken()->getUser()->getEmail();
    }

    public function onFosuserProfileEditSuccess(FormEvent $event)
    {
        /** @var User $user */
        $user = $event->getForm()->getData();
        if ($user->getEmail() !== $this->email) {
            $user->setEmailVerified(false);
        }
        $this->doctrineManager->persist($user);
        if (!empty($user->imgBase64)) {

            $file = tmpfile();
            if ($file === false)
                throw new \Exception('File can not be opened.');

            $fileName = uniqid();
            $content = base64_decode($user->imgBase64);

            $path = $this->rootDir . "/../web/media/img/{$fileName}.jpg";
            file_put_contents($path, $content);


            $uploadedFile = new UploadedFile($path, "{$fileName}.jpg", null, null, null, true);
            $this->uploadableManager->markEntityToUpload($user, $uploadedFile);

            try {
                $this->doctrineManager->flush();
            } catch (UploadableException $e) {
                $user->revertImage();
            }

        }
        if ($user->imgIsChanged()) {
            $fileInfo = $user->getImgPath();
            $this->uploadableManager->markEntityToUpload($user, $fileInfo);
            try {
                $this->doctrineManager->flush();
            } catch (UploadableException $e) {
                $user->revertImage();
            }
        }
        $tab = $event->getRequest()->get('tab-switch', '');
        $event->setResponse(
            new RedirectResponse($this->route->generate('fos_user_profile_edit', $tab ? ['tab' => $tab] : []))
        );
    }

    public function onFosuserProfileEditCompleted(FilterUserResponseEvent $event){

    }
}