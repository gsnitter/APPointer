<?php declare(strict_types = 1);

namespace APPointer\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TodoRepository extends EntityRepository
{
    public function findPossiblyDueTodosQB(): QueryBuilder
    {
        // All todos with display interval bigger than x and now <= date
        // OR display interval smaller x and now element of [date - x, date],
        // that is: date <= now + x and date >= now
        $qb = $this->createQueryBuilder('t');
        $nowString = date('Y-m-d H:i:s');

        $query = $qb->select('t')
            ->where($qb->expr()->andX(
                $qb->expr()->gt('t.displayInterval', ':interval'),
                $qb->expr()->gte('t.date', ':now')
            ))
            ->orWhere($qb->expr()->andX(
                $qb->expr()->lte('t.displayInterval', ':interval'),
                $qb->expr()->lte('t.date', ':nowPlusX'),
                $qb->expr()->gte('t.date', ':now')
           ))
            ->orderBy('t.date', 'DESC')
            ->setParameter('interval', '+P00Y00M20DT00H00M00S')
            ->setParameter('now', new \DateTime())
            ->setParameter('nowPlusX', new \DateTime('+20 days'))
            ;

        return $qb;
    }

    public function findDueTodos()
    {
        $result = $this->findPossiblyDueTodosQB()->getQuery()->getResult();
        $result = array_filter($result, function($todo) {
            return $todo->isDue();
        });
        return $result;
    }
}

