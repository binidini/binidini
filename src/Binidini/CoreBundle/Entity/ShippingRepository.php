<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Query\Expr\OrderBy;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ShippingRepository extends EntityRepository
{

    public function findByArguments($id, $state, $deliveryPriceFrom, $deliveryPriceTo, $insurancePriceFrom, $insurancePriceTo, $deliveryTimeFrom, $deliveryTimeTo)
    {

        $queryBuilder = $this->getCollectionQueryBuilder('s');
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

        $queryBuilder->addOrderBy($this->getPropertyName('id'), 'desc');

        return $this->getPaginator($queryBuilder);
    }
}