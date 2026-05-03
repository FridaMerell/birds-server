<?php

namespace App\Repository\Taxon;

use App\Entity\Card;
use App\Entity\Taxon\Family;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Family>
 *
 * @method Family|null find($id, $lockMode = null, $lockVersion = null)
 * @method Family|null findOneBy(array $criteria, array $orderBy = null)
 * @method Family[]    findAll()
 * @method Family[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FamilyRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct(registry: $registry, entityClass: Family::class);
	}

	public function add(Family $entity, bool $flush = false): void
	{
		$this->getEntityManager()->persist($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Family $entity, bool $flush = false): void
	{
		$this->getEntityManager()->remove($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	/**
	 * @throws QueryException
	 */
	function findSpeciesFromCard(Card $card)
	{
		$qb = $this->createQueryBuilder('qb')
			->innerJoin(join: 'qb.Genus', alias: 'genus')
			->join(join: 'genus.species', alias: 'species')
			->innerJoin(
				join: 'species.Cards',
				alias: 'cards'
			)
			->addSelect('genus')
			->addSelect('species')
			->addSelect('cards')
			->where('cards in (:card)')
			->setParameter(key: 'card', value: $card);

		return $qb->getQuery()->getResult();
	}

	//    /**
//     * @return Family[] Returns an array of Family objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

	//    public function findOneBySomeField($value): ?Family
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
