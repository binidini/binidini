<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Binidini\CoreBundle\EventListener;

use Binidini\CoreBundle\Service\NotificationService;
use Binidini\SearchBundle\Document\Shipment;
use Binidini\SearchBundle\Document\ShipmentItem;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Event\ResourceEvent;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Listener responsible to copy shipping object into mongodb
 */
class ShippingListener
{
    private $dm;
    private $em;
    private $geocodeProducer;
    private $logger;
    private $rootDir;
    private $ns;

    public function __construct(DocumentManager $documentManager, EntityManager $em, Producer $geocodeProducer, LoggerInterface $logger, $rootDir, NotificationService $ns)
    {
        $this->dm = $documentManager;
        $this->em = $em;
        $this->geocodeProducer = $geocodeProducer;
        $this->logger = $logger;
        $this->rootDir = $rootDir;
        $this->ns = $ns;
    }


    public function onShippingPreCreate(ResourceEvent $event)
    {
        /**
         * @var \Binidini\CoreBundle\Entity\Shipping $shipping
         */
        $shipping = $event->getSubject();

        if (is_null($shipping->getInsurance())) {
            $shipping->setInsurance(0);
        }
    }

    /**
     * Move photo to upload dir
     * Copy shipping into mongodb
     */
    public function onShippingPostCreate(ResourceEvent $event)
    {
        /**
         * @var \Binidini\CoreBundle\Entity\Shipping $shipping
         */
        $shipping = $event->getSubject();

        if (!empty($shipping->imgBase64)) {

            try {
                $file = tmpfile();
                if ($file === false)
                    throw new \Exception('File can not be opened.');

                $filename = 'tytymyty_' . $shipping->getId() .uniqid().".jpg";
                $content = base64_decode($shipping->imgBase64);

                $path = $this->rootDir . "/../web/media/img/parcels/{$filename}";
                file_put_contents($path, $content);


                $shipping->setImgPath('parcels/' . $filename);
                $this->em->flush($shipping);
            } catch(\Exception $ex){
                $this->logger->critical('Не удалось загрузить файл.' . $ex->getMessage() . '.' . $ex->getTraceAsString());
            }
        }

        if (!empty($shipping->imgFile)) {
            try {
                $filename = 'tytymyty_' . $shipping->getId() . '.' . $shipping->imgFile->guessExtension();
                $shipping->imgFile->move(
                    $this->rootDir . "/../web/media/img/parcels/",
                    $filename
                );
                $shipping->setImgPath('parcels/'.$filename);
                $this->em->flush($shipping);
            } catch (\Exception $ex) {
                $this->logger->critical('Не удалось загрузить файл.' . $ex->getMessage() . '.' . $ex->getTraceAsString());
            }

        }

        $shipment = new Shipment();
        $shipment
            ->setId($shipping->getId())
            ->setDeliveryPrice($shipping->getDeliveryPrice())
            ->setPaymentGuarantee($shipping->getPaymentGuarantee())
            ->setDeliveryAddress($shipping->getDeliveryAddress())
            ->setDeliveryDatetime($shipping->getDeliveryDatetime())
            ->setPickupAddress($shipping->getPickupAddress())
            ->setName($shipping->getName())
            ->setDescription($shipping->getDescription())
            ->setWeight($shipping->getWeight())
            ->setInsurance($shipping->getInsurance())
            ->setX($shipping->getX())
            ->setY($shipping->getY())
            ->setZ($shipping->getZ())
            ->setImgPath($shipping->getImgPath())
        ;

        $this->dm->persist($shipment);
        $this->dm->flush();

        $msg = array('id' => $shipping->getId());
        $this->geocodeProducer->publish(serialize($msg));

        //temporary stub
        $this->ns->notifyInsidersAboutNewDffShipping($shipping);
    }

}