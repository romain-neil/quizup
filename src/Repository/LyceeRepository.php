<?php
namespace App\Repository;

use App\Entity\Lycee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lycee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lycee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lycee[]    findAll()
 * @method Lycee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LyceeRepository extends ServiceEntityRepository {

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Lycee::class);
	}

}
