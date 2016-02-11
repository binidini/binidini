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

    //https://github.com/binidini/tytymyty-ios/issues/35
    public function search2Action(Request $request)
    {
        $sort = $request->get('sort', 'delivery_time');
        $searchType = $request->get('search_type', 'pickup');
        $longitude = $request->get('lon');
        $latitude  = $request->get('lat');
        $searchAddress = $request->get('top-search');

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

        if (is_null($longitude) || is_null($latitude)) {
            $shipments = $this->getRepository()->findAll();
        } else {
            $shipments = $this->getRepository()->findByLoc($longitude, $latitude);
        }

        //$shipments->setCurrentPage($request->get('page', 1), true, true);
        //$shipments->setMaxPerPage($this->config->getPaginationMaxPerPage());

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


    /**
     * @return MyPagerfantaFactory
     */
    protected function getMyPagerfantaFactory()
    {
        return new MyPagerfantaFactory();
    }

}