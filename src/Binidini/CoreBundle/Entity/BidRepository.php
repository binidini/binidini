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

namespace Binidini\CoreBundle\Entity;


use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class BidRepository extends EntityRepository
{

    public function findLastBids($id, $state, $deliveryPriceFrom, $deliveryPriceTo, $insurancePriceFrom, $insurancePriceTo, $deliveryTimeFrom, $deliveryTimeTo)
    {

        $queryBuilder = $this->getCollectionQueryBuilder();
        if ($id) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('id'), ':id'))
                ->setParameter(':id', $id);
        }
        if ($state) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('state'), ':st'))
                ->setParameter(':st',$state);

        }
        if ($deliveryPriceFrom) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('deliveryPrice'), ':dpf'))
                ->setParameter(':dpf', $deliveryPriceFrom);
        }
        if ($deliveryPriceTo) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte($this->getPropertyName('deliveryPrice'), ':dpt'))
                ->setParameter(':dpt', $deliveryPriceTo);
        }
        if ($insurancePriceFrom) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('insurance'), ':inf'))
                ->setParameter(':inf', $insurancePriceFrom);
        }
        if ($insurancePriceTo) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte($this->getPropertyName('insurance'), ':int'))
                ->setParameter(':int', $insurancePriceTo);
        }
        if ($deliveryTimeFrom) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('deliveryDatetime'), ':dtf'))
                ->setParameter(':dtf', $deliveryTimeFrom);
        }
        if ($deliveryTimeTo) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte($this->getPropertyName('deliveryDatetime'), ':dtt'))
                ->setParameter(':dtt', $deliveryTimeTo);
        }

        return $this->getPaginator($queryBuilder);
    }
}