<?php
namespace App\Repository;

use App\Entity\Choice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Choice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Choice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Choice[]    findAll()
 * @method Choice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChoiceRepository extends ServiceEntityRepository {

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Choice::class);
	}

}
