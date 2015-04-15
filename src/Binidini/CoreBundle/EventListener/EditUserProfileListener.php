<?php

namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class EditUserProfileListener implements EventSubscriberInterface
{

    /**
     * @var UploadableManager
     */
    private $uploadableManager;
    /**
     * @var EntityManager
     */
    private $doctrineManager;

    private $email;

    private $security;

    public function __construct(UploadableManager $uploadableManager, EntityManager $doctrine, SecurityContextInterface $security)
    {
        $this->uploadableManager = $uploadableManager;
        $this->doctrineManager = $doctrine;
        $this->security = $security;
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
    }

    public function onFosuserProfileEditCompleted(FilterUserResponseEvent $event){

        /** @var User $user */
        $user = $event->getUser();
        $this->doctrineManager->persist($user);
        if ($user->imgIsChanged()) {
            $fileInfo = $user->getImgPath();
            $this->uploadableManager->markEntityToUpload($user, $fileInfo);
            $this->doctrineManager->flush();
        }

    }
}