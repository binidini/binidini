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

namespace Binidini\SearchBundle\Document;


use Sylius\Bundle\ResourceBundle\Doctrine\ODM\MongoDB\DocumentRepository;

class ShipmentRepository extends DocumentRepository
{
    public function findByLocation($longitude, $latitude)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        if ( is_numeric($longitude) && is_numeric($latitude) ) {
            $queryBuilder->field('pickupCoordinates')->near((float)$longitude, (float)$latitude);
        }

        return $this->getPaginator($queryBuilder);
    }

    public function findByLoc($longitude, $latitude, $type = 'pickup', $sort = 'delivery_price')
    {
        $queryBuilder = $this->getCollectionQueryBuilder()->sort($sort);

        /*if ( is_numeric($longitude) && is_numeric($latitude) ) {
            $queryBuilder->field($type.'Coordinates')->geoNear((float)$longitude, (float)$latitude)
            ->spherical(true)->distanceMultiplier(6378.137)->sort($sort);
        }*/

        return $queryBuilder->getQuery()->execute();
    }
}