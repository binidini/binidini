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

    public function findByLoc($longitude, $latitude, $searchType = 'pickup', $sort = 'deliveryPrice')
    {
        $s = $this->findAll();


        return $s->toArray();

        return $queryBuilder->getQuery()->execute()->toArray();
//        return $this->getPaginator($queryBuilder);
    }
}