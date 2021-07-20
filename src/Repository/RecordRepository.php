<?php
namespace App\Repository;

use App\Entity\Record;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Record|null find($id, $lockMode = null, $lockVersion = null)
 * @method Record|null findOneBy(array $criteria, array $orderBy = null)
 * @method Record[]    findAll()
 * @method Record[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecordRepository extends ServiceEntityRepository {

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Record::class);
	}

	/**
	 * Retourne les enregistrements avec l'id de participation $id
	 * @param $id
	 * @return int|mixed|string
	 */
	public function findByParticipationId($id) {
		return $this->createQueryBuilder('r')
			->andWhere('r.participation_id = :pid')
			->setParameter('pid', $id)
			->orderBy('r.id', 'ASC')
			->getQuery()
			->getResult();
	}

}
