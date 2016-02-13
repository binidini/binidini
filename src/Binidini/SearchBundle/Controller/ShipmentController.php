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
        $sort = $request->get('sort', 'delivery_datetime');
        $latitude  = $request->get('lat', 55.7522);
        $longitude = $request->get('lon', 37.6156);
        $searchAddress = $request->get('top-search', null);

        //<-- Ведем базу передвижения пользователя
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
        // -->

        #<-- Запрашиваем координаты адреса в яндекс картах
        if ( !empty($searchAddress) ) {

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
        # -->

        $orderBy = [];
        if ($sort == 'delivery_time') {
            $orderBy = ['deliveryDatetime' => 'asc'];
        } elseif ($sort == 'delivery_price') {
            $orderBy = ['deliveryPrice' => 'desc'];
        }

        $shipments = $this->getRepository()->findBy([], $orderBy);

        /** @var $shipment \Binidini\SearchBundle\Document\Shipment */
        foreach ($shipments as $shipment) {

            if (is_null($shipment->getPickupCoordinates())) {
                $pickupDistance = 20000000;
            } else {
                $pickupDistance = $this->circle_distance($latitude, $longitude, $shipment->getPickupCoordinates()->getLatitude(), $shipment->getPickupCoordinates()->getLongitude());
            }

            if (is_null($shipment->getDeliveryCoordinates())) {
                $deliveryDistance = 20000000;
            } else {
                $deliveryDistance = $this->circle_distance($latitude, $longitude, $shipment->getDeliveryCoordinates()->getLatitude(), $shipment->getDeliveryCoordinates()->getLongitude());
            }
            $result[] = ['pickup_distance' => intval($pickupDistance), 'delivery_distance' => intval($deliveryDistance), 'order' => $shipment];
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