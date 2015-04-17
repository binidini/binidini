<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\CoreBundle\Worker;

use Binidini\SearchBundle\Document\Coordinates;
use Binidini\SearchBundle\Document\Shipment;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Guzzle\Service\ClientInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Proxies\__CG__\Binidini\CoreBundle\Entity\Shipping;


class GeocodeWorker implements ConsumerInterface
{
    private $geocode;
    private $em;
    private $dm;

    public function __construct(ClientInterface $geocode, EntityManager $em, DocumentManager $dm)
    {
        $this->geocode = $geocode;
        $this->em = $em;
        $this->dm = $dm;
    }

    public function execute(AMQPMessage $msg)
    {
        $pLong = null;
        $pLat = null;
        $dLong = null;
        $dLat = null;

        $data = unserialize($msg->body);

        /** @var Shipping $shipping */
        $shipping = $this->em->find('\Binidini\CoreBundle\Entity\Shipping', $data['id']);

        // Координаты адреса отправки
        $params = array(
            'geocode' => $shipping->getPickupAddress(),   // адрес
            'format'  => 'json',                          // формат ответа
            'results' => 1,                               // количество выводимых результатов
        );
        $res = $this->geocode->get('?'.http_build_query($params, '', '&'))->send();

        if ($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody(true));

            if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
            {
                list($pLong, $pLat) = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
            }
        }

        // Координаты адреса доставки
        $params = array(
            'geocode' => $shipping->getDeliveryAddress(),  // адрес
            'format'  => 'json',                          // формат ответа
            'results' => 1,                               // количество выводимых результатов
        );
        $res = $this->geocode->get('?'.http_build_query($params, '', '&'))->send();

        if ($res->getStatusCode() == 200) {
            $response = json_decode($res->getBody(true));

            if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
            {
                list($dLong, $dLat) = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
            }
        }

        $shipping->setPickupLongitude($pLong);
        $shipping->setPickupLatitude($pLat);
        $shipping->setDeliveryLongitude($dLong);
        $shipping->setdeliveryLatitude($dLat);

        $this->em->flush($shipping);

        /** @var  Shipment $shipment */
        $shipment = $this->dm->find('\Binidini\SearchBundle\Document\Shipment', $shipping->getId());

        if ($shipment) {

            $pLoc = new Coordinates($pLong, $pLat);
            $shipment->setPickupCoordinates($pLoc);

            $dLoc = new Coordinates($dLong, $dLat);
            $shipment->setDeliveryCoordinates($dLoc);

            $this->dm->flush($shipment);
        }

    }
}