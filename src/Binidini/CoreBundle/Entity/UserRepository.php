<?php

namespace Binidini\CoreBundle\Entity;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{

    public function findByArguments($phone, $lastName, $firstName, $parentName, $email, $registrationFrom, $registrationTo, $countOfCarriers, $countOfSenders, $blocked, $unBlocked)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        if ($phone) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('username'), ':un'))
                ->setParameter(':un', $phone);
        }
        if ($lastName) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('lastName'), ':ln'))
                ->setParameter(':ln',$lastName);

        }
        if ($firstName) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('firstName'), ':fn'))
                ->setParameter(':fn', $firstName);
        }
        if ($parentName) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('patronymic'), ':pn'))
                ->setParameter(':pn', $parentName);
        }
        if ($email) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('email'), ':em'))
                ->setParameter(':em', $email);
        }
        if ($registrationFrom) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte($this->getPropertyName('createdAt'), ':rgf'))
                ->setParameter(':rgf', $registrationFrom);
        }
        if ($registrationTo) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte($this->getPropertyName('createdAt'), ':rgt'))
                ->setParameter(':rgt', $registrationTo);
        }
        if ($countOfCarriers) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('carrierCount'), ':cc'))
                ->setParameter(':cc', $countOfCarriers);
        }
        if ($countOfSenders) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('senderCount'), ':sc'))
                ->setParameter(':sc', $countOfSenders);
        }
        if ($blocked || $unBlocked) {
            $getBlocked = $blocked == 'on';
            $getUnblocked = $unBlocked == 'on';
            if ($getBlocked && $getUnblocked) {
                //Выбираем все возможные статусы блокировки
            } else {
                if ($getBlocked) {
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('locked'), ':locked'))
                        ->setParameter(':locked', 1);
                } elseif ($getUnblocked) {
                    $queryBuilder->andWhere($queryBuilder->expr()->eq($this->getPropertyName('locked'), ':locked'))
                        ->setParameter(':locked', 0);
                }
            }

        }
        return $this->getPaginator($queryBuilder);
    }

    //http://stackoverflow.com/questions/2234204/latitude-longitude-find-nearest-latitude-longitude-complex-sql-or-complex-calc
    //пока отсылаем 50 курьерам сообщение, которые были возле

    public function findByCoordinates($lon, $lat) {

        $params = [
            'lon' => $lon,
            'lat' => $lat
        ];

        $sql = 'SELECT id FROM user u WHERE u.type !=2 AND (POW(69.1 * (u.latitude - :lat), 2) + POW(69.1 * (:lon - u.longitude) * COS(u.latitude / 57.3), 2)) < 4 ORDER BY u.updated_at DESC LIMIT 51';

        $em = $this->getEntityManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();

    }
}