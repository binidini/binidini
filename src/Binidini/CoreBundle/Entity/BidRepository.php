<?php
/*
 * This file is part of the Binidini project.
 *
 * (c) Denis Manilo
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Binidini\CoreBundle\Entity;


use Doctrine\ORM\Query\Expr\OrderBy;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class BidRepository extends EntityRepository
{

    public function findLast5DaysNewBids($sender)
    {

        $queryBuilder = $this->getCollectionQueryBuilder('o');

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('sender'), ':sender'))
            ->setParameter(':sender', $sender);

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('state'), ':state'))
            ->setParameter(':state', 'new');

        $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('createdAt'), ':createdAt'))
            ->setParameter(':createdAt', new \DateTime('5 days ago'));

        $queryBuilder->addOrderBy($this->getPropertyName('id'), 'desc');

        return $this->getPaginator($queryBuilder);
    }

    public function findLast5DaysAcceptedBids($user)
    {

        $queryBuilder = $this->getCollectionQueryBuilder('o');

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('user'), ':user'))
            ->setParameter(':user', $user);

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('state'), ':state'))
            ->setParameter(':state', 'accepted');

        $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('createdAt'), ':createdAt'))
            ->setParameter(':createdAt', new \DateTime('5 days ago'));

        $queryBuilder->addOrderBy($this->getPropertyName('updatedAt'), 'desc');

        return $this->getPaginator($queryBuilder);
    }
}