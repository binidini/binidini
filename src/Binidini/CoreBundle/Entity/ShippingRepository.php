<?php

namespace Binidini\CoreBundle\Entity;

use Doctrine\ORM\Query\Expr\OrderBy;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findByCarrierIdAndStates($carrierId, $states)
    {
        $queryBuilder = $this->getCollectionQueryBuilder('s');
        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('carrier'), ':carrier_id'))
            ->setParameter(':carrier_id', $carrierId);
        $queryBuilder->andWhere($queryBuilder->expr()->in($this->getPropertyName('state'), ':st'))
            ->setParameter(':st', $states);
        $queryBuilder->addOrderBy($this->getPropertyName('id'), 'desc');
        return $this->getPaginator($queryBuilder);
    }

    public function findBySenderIdAndStates($senderId, $states)
    {
        $queryBuilder = $this->getCollectionQueryBuilder('s');
        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('user'), ':sender_id'))
            ->setParameter(':sender_id', $senderId);
        $queryBuilder->andWhere($queryBuilder->expr()->in($this->getPropertyName('state'), ':st'))
            ->setParameter(':st', $states);
        $queryBuilder->addOrderBy($this->getPropertyName('id'), 'desc');
        $queryBuilder->setMaxResults(40);

        return $this->getPaginator($queryBuilder);
    }
}