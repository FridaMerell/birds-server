<?php

namespace App\Repository;

use App\Entity\Taxon\Species;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Species>
 *
 * @method Species|null find($id, $lockMode = null, $lockVersion = null)
 * @method Species|null findOneBy(array $criteria, array $orderBy = null)
 * @method Species[]    findAll()
 * @method Species[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpeciesRepository extends ServiceEntityRepository {
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Species::class);
	}

	public function save(Species $entity, bool $flush = false): void {
		$this->getEntityManager()->persist($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Species $entity, bool $flush = false): void {
		$this->getEntityManager()->remove($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function search(string $term) {
		$qb = $this->createQueryBuilder('s');

		$qb
			->where($qb->expr()->like('s.VernacularName', ':term'))
			->orWhere($qb->expr()->like('s.ScientificName', ':term'));

		$qb->setMaxResults(10);
		$qb->setFirstResult(0);
		$qb->leftJoin('s.Sightings', 'sightings');
		$qb->addSelect('COUNT(sightings.id) as num_sightings');
		$qb->groupBy('sightings.species');
		$this->orderBySwedishProminence($qb, 's');
		$qb->addOrderBy('num_sightings', 'DESC');
		$qb->setParameter('term', '%' . $term . '%');

		return $qb->getQuery()->getResult();
	}

	private function orderBySwedishProminence(&$qb, $alias) {
		$qb
			->setParameters([
				'common' => 'Bofast och reproducerande',
				'regular' => 'Regelbunden förekomst, ej reproducerande',
				'temporary' => 'Ej bofast men tillfälligt reproducerande',
				'occasional' => 'Tillfällig förekomst (alt. kvarstående)',
				'former' => 'Ej längre bofast, nu endast tillfälligt förekommande',
				'uncertain' => 'Osäkert om påträffad',
				'notfound' => 'Ej påträffad',
				'empty' => ''
			])
			->addSelect("(CASE WHEN {$alias}.SwedishProminence 
		LIKE :common THEN 1 
		WHEN {$alias}.SwedishProminence  LIKE :regular THEN 2 
		WHEN {$alias}.SwedishProminence  LIKE :temporary THEN 3
		WHEN {$alias}.SwedishProminence  LIKE :occasional THEN 4
		WHEN {$alias}.SwedishProminence  LIKE :former THEN 5
		WHEN {$alias}.SwedishProminence  LIKE :uncertain THEN 6
		WHEN {$alias}.SwedishProminence  LIKE :notfound THEN 7
		WHEN {$alias}.SwedishProminence  LIKE :empty THEN 8
		ELSE 9
		END) as HIDDEN ORD");


		$qb->orderBy('ORD', 'ASC');
	}

}
