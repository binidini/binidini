<?php

namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Gedmo\Exception\UploadableException;
use Gedmo\Uploadable\FileInfo\FileInfoInterface;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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

    public function __construct(UploadableManager $uploadableManager, EntityManager $doctrine, SecurityContextInterface $security, RouterInterface $route)
    {
        $this->uploadableManager = $uploadableManager;
        $this->doctrineManager = $doctrine;
        $this->security = $security;
        $this->route = $route;
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
        try {
            $fileInfo = $user->getImgPath();
            if ($fileInfo instanceof FileInfoInterface){
                $this->uploadableManager->markEntityToUpload($user, $fileInfo);
                $this->doctrineManager->flush();
            }
        } catch (UploadableException $e) {
            $user->revertImage();
        }

        $tab = $event->getRequest()->get('tab-switch', '');
        $event->setResponse(
            new RedirectResponse($this->route->generate('fos_user_profile_edit', $tab ? ['tab' => $tab] : []))
        );
    }

    public function onFosuserProfileEditCompleted(FilterUserResponseEvent $event){

    }
}