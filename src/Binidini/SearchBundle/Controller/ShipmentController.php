<?php

/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\SearchBundle\Controller;


use Binidini\CoreBundle\Entity\Location;
use Binidini\SearchBundle\Document\Shipment;
use Binidini\SearchBundle\Document\ShipmentRepository;
use Binidini\SearchBundle\Model\MyPagerfantaFactory;
use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Hateoas\Configuration\Route;
use Binidini\CoreBundle\Entity\User;

class ShipmentController extends ResourceController
{
    public function searchAction(Request $request)
    {

        $longitude = $request->get('lon');
        $latitude  = $request->get('lat');
        $searchAddress = $request->get('top-search');

        if (!is_null($longitude) && !is_null($latitude)) {
            /** @var $user User */
            $user = $this->getUser();

            if (!is_null($user)) {

                $loc = new Location();
                $loc
                    ->setUser($user)
                    ->setLatitude($latitude)
                    ->setLongitude($longitude)
                ;
                $this->getDoctrine()->getManager()->persist($loc);

                $user->setLatitude($latitude);
                $user->setLongitude($longitude);

                $this->getDoctrine()->getManager()->flush();
            }
        }

        if ( !empty($searchAddress) && (is_null($longitude) || is_null($latitude))) {

            $geocode = $this->container->get('binidini.geocode.yandex.client');

            $params = array(
                'geocode' => $searchAddress,
                'format'  => 'json',
                'results' => 1,
            );
            $res = $geocode->get('?'.http_build_query($params, '', '&'))->send();

            if ($res->getStatusCode() == 200) {
                $response = json_decode($res->getBody(true));
                if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
                    list($longitude, $latitude) = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                }
            }

        }

        if (is_null($longitude) || is_null($latitude)) {
            $shipments = $this->getRepository()->createPaginator();
        } else {
            $shipments = $this->getRepository()->findByLocation($longitude, $latitude);
        }

        $shipments->setCurrentPage($request->get('page', 1), true, true);
        $shipments->setMaxPerPage($this->config->getPaginationMaxPerPage());

        if ($this->config->isApiRequest()) {

            $shipments = $this->getMyPagerfantaFactory()->createRepresentation(
                $shipments,
                new Route(
                    $request->attributes->get('_route'),
                    $request->attributes->get('_route_params')
                )
            );
        } else {
            if (!empty($searchAddress)) {
                $this->flashHelper->setFlash('success', 'top_search', ['%address%' => $searchAddress]);
            }
        }

        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate(''))
            ->setTemplateVar($this->config->getPluralResourceName())
            ->setData($shipments);

        return $this->handleView($view);

    }

    public function distanceAction(Request $request)
    {
        $searchAddress = $request->get('top-search', null);

        $latitude  = $request->get('lat');
        $longitude = $request->get('lon');
        //Ведем базу передвижения пользователя
        /** @var $user User */
        $user = $this->getUser();
        if ($longitude && $latitude) {
            if ($user) {
                $loc = new Location();
                $loc->setUser($user)
                    ->setLatitude($latitude)
                    ->setLongitude($longitude);
                $this->getDoctrine()->getManager()->persist($loc);
                $user->setLatitude($latitude);
                $user->setLongitude($longitude);
                $this->getDoctrine()->getManager()->flush();
            }
        }
        //Запрашиваем координаты адреса в яндекс картах
        if (!empty($searchAddress)) {
            $geocode = $this->container->get('binidini.geocode.yandex.client');
            $params = array(
                'geocode' => $searchAddress,
                'format' => 'json',
                'results' => 1,
            );
            $res = $geocode->get('?' . http_build_query($params, '', '&'))->send();
            if ($res->getStatusCode() == 200) {
                $response = json_decode($res->getBody(true));
                if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
                    list($longitude, $latitude) = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                }
            }
        }
        /** @var $repository ShipmentRepository */
        $repository = $this->getRepository();
        if (!$longitude || !$latitude) {
            if ($user) {
                $longitude = $user->getLongitude();
                $latitude = $user->getLatitude();
            }
            if (!$longitude || !$latitude) {
                $latitude = 59.9500;
                $longitude = 30.3000;
            }
        }
        $shipments = $repository->findByLocation($longitude, $latitude);
        $shipments->setCurrentPage($request->get('page', 1));
        $shipments->setMaxPerPage(500);
        $result = array();
        /** @var $shipment Shipment */
        foreach ($shipments->getIterator() as $shipment) {
            if ($shipment->getPickupCoordinates()) {
                $latDiff = $shipment->getPickupCoordinates()->getLatitude() - $latitude;
                $lonDiff = $shipment->getPickupCoordinates()->getLongitude() - $longitude;
                $pickupDistance = sqrt(pow($latDiff, 2) + pow($lonDiff, 2));
            } else {
                $pickupDistance = 0;
            }
            if ($shipment->getDeliveryCoordinates()) {
                $latDiff = $shipment->getDeliveryCoordinates()->getLatitude() - $latitude;
                $lonDiff = $shipment->getDeliveryCoordinates()->getLongitude() - $longitude;
                $deliveryDistance = sqrt(pow($latDiff, 2) + pow($lonDiff, 2));
            } else {
                $deliveryDistance = 0;
            }

            $result[] = [
                'id' => $shipment->getId(),
                'name' => $shipment->getName(),
                'delivery_price' => $shipment->getDeliveryPrice(),
                'guarantee' => $shipment->getGuarantee(),
                'insurance' => $shipment->getInsurance(),
                'pickup_address' => $shipment->getPickupAddress(),
                'delivery_address' => $shipment->getDeliveryAddress(),
                'payment_guarantee' => $shipment->getPaymentGuarantee(),
                'delivery_datetime' => $shipment->getDeliveryDatetime(),
                'delivery_distance' => intval(111000 * $deliveryDistance),
                'pickup_distance' => intval(111000 * $pickupDistance),
            ];
        }
        $view = $this
            ->view()
            ->setTemplate($this->config->getTemplate(''))
            ->setTemplateVar($this->config->getPluralResourceName())
            ->setData($result);

        return $this->handleView($view);

    }


    /**
     * @return MyPagerfantaFactory
     */
    private function getMyPagerfantaFactory()
    {
        return new MyPagerfantaFactory();
    }

    private function circle_distance($lat1, $lon1, $lat2, $lon2) {
        $rad = M_PI / 180;
        return acos(sin($lat2*$rad) * sin($lat1*$rad) + cos($lat2*$rad) * cos($lat1*$rad) * cos($lon2*$rad - $lon1*$rad)) * 6371000;// Kilometers*1000 = meters
    }

}