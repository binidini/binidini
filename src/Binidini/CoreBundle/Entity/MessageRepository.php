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

class MessageRepository extends EntityRepository
{
    public function findLast15DaysMyNewMessages ($resipient)
    {

        $queryBuilder = $this->getCollectionQueryBuilder('m');

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('recipient'), ':recipient'))
            ->setParameter(':recipient', $resipient);

        $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('state'), ':state'))
            ->setParameter(':state', 'new');

        $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('createdAt'), ':createdAt'))
            ->setParameter(':createdAt', new \DateTime('15 days ago'));

        $queryBuilder->addOrderBy($this->getPropertyName('id'), 'desc');

        return $this->getPaginator($queryBuilder);
    }
}