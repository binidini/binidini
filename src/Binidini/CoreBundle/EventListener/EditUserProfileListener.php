<?php

namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Stof\DoctrineExtensionsBundle\Uploadable\UploadableManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    public function __construct(UploadableManager $uploadableManager, EntityManager $doctrine)
    {
        $this->uploadableManager = $uploadableManager;
        $this->doctrineManager = $doctrine;
    }

    public static function getSubscribedEvents()
    {
        return [];
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