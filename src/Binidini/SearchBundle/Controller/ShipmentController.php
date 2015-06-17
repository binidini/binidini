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

namespace Binidini\SearchBundle\Controller;


use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\Request;
use Hateoas\Configuration\Route;

class ShipmentController extends ResourceController
{
    public function searchAction(Request $request)
    {
        $searchAddress = $request->get('top-search');

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
            } else {
                return $this->redirectHandler->redirectToIndex();
            }
        } else {
            return $this->redirectHandler->redirectToIndex();
        }

        $shipments = $this->getRepository()->findByLocation($longitude, $latitude);
        $shipments->setCurrentPage($request->get('page', 1), true, true);
        $shipments->setMaxPerPage($this->config->getPaginationMaxPerPage());

        if ($this->config->isApiRequest()) {
            $shipments = $this->getPagerfantaFactory()->createRepresentation(
                $shipments,
                new Route(
                    $request->attributes->get('_route'),
                    $request->attributes->get('_route_params')
                )
            );
        } else {
            $this->flashHelper->setFlash('success', 'top_search', ['%address%' => $searchAddress]);
        }

        $view = $this
            ->view()
            ->setTemplate('BinidiniWebBundle::Frontend/Shipment/search.html.twig')
            ->setTemplateVar($this->config->getPluralResourceName())
            ->setData($shipments);

        return $this->handleView($view);

    }
}