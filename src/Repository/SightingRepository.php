<?php

namespace App\Repository;

use App\Entity\Card;
use App\Entity\Sighting;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Sighting>
 *
 * @method Sighting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sighting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sighting[]    findAll()
 * @method Sighting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SightingRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry){
		parent::__construct($registry, Sighting::class);
	}

	public function add(Sighting $entity, bool $flush = false): void{
		$this->getEntityManager()->persist($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Sighting $entity, bool $flush = false): void{
		$this->getEntityManager()->remove($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	function findOrderedCard(Card $card): array{
		$qb = $this->createQueryBuilder('q');
		$this->addCookieFiltering($qb);
		$qb->innerJoin('q.Cards', 'cards')
			->andWhere('cards IN (:card)')
			->setParameter('card', $card);
		return $qb->getQuery()->getResult();
	}

	function findOrderedList(?User $user = null, ?int $page = null, ?int $maxResults = null): array{
		$qb = $this->createQueryBuilder('q');

		//Pagination
		if ($maxResults)
			$qb->setMaxResults($maxResults);
		if ($page)
			$qb->setFirstResult($page * ($maxResults??1));

		if ($user) {
			$qb->andWhere('q.User = :user')
				->setParameter('user', $user);
		}

		$this->addCookieFiltering($qb);

		$qb->orderBy('q.DateTime', 'DESC');


		return $qb->getQuery()->getResult();
	}

	/**
	 * @param QueryBuilder $qb
	 * @return QueryBuilder
	 */
	private function addCookieFiltering(QueryBuilder $qb, ?string $alias = 'q'): QueryBuilder{
		//Filter and order list by cookie values from filter dialog
		$request = Request::createFromGlobals();
		$cookies = $request->cookies;

		if ($order = $cookies->get('order')) {
			if ($order == 'az') {
				$qb->leftJoin($alias . '.species', 'species')
					->orderBy('species.VernacularName', 'ASC');
			} else {
				$qb->orderBy(new OrderBy($alias . '.id', 'DESC'));
			}
		} else {
			$orderBy = new OrderBy($alias . '.id', 'DESC');
			$qb->orderBy($orderBy);
		}

		$distinct = $cookies->get('distinct');
		if ($distinct == 'true') {
			$qb
				->groupBy($alias . '.species')
				->distinct();
		}

		$year = $cookies->get('year');
		if ($year && $year != -1) {
			$yearStart = \DateTime::createFromFormat('Y-m-d H:i', $year . '-01-01 00:00');
			$yearEnd = \DateTime::createFromFormat('Y-m-d H:i', $year . '-12-31 23:59');
			$qb->andWhere($alias . '.DateTime BETWEEN :start AND :end')
				->setParameter('start', $yearStart)
				->setParameter('end', $yearEnd);
		}

		return $qb;
	}
//    /**
//     * @return Sighting[] Returns an array of Sighting objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sighting
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
