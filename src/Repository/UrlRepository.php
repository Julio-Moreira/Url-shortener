<?php

namespace App\Repository;

use App\Entity\Url;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Url>
 *
 * @method Url|null find($id, $lockMode = null, $lockVersion = null)
 * @method Url|null findOneBy(array $criteria, array $orderBy = null)
 * @method Url[]    findAll()
 * @method Url[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UrlRepository extends ServiceEntityRepository implements UrlRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Url::class);
    }

    public function get(string $labelWithId): ?Url
    {
        $strHasLabel = strpos($labelWithId, '.') >= 0;
        $id = $strHasLabel ? $labelWithId : explode('.', $labelWithId, 1)[1];

        return $this
            ->findOneBy(['id' => $id]);
    }

    public function save(Url $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush)
            $this->getEntityManager()->flush();
    }

    public function removeById(string $id): void
    {
        $url = $this->getEntityManager()
            ->getPartialReference(Url::class, $id);
        $this->remove($url);
    }

    public function remove(Url $entity, bool $flush = true): void
    {
        $this->removeUrlInUser($entity);
        $this->getEntityManager()->remove($entity);

        if ($flush)
            $this->getEntityManager()->flush();
    }
    
    private function removeUrlInUser(Url $entity): void
    {
        if ($this->urlHasUser($entity)) {
            $user = $entity->getUser();
            $user->removeUrl($entity);
        }
    }

    private function urlHasUser(Url $url): bool
    {
        return is_null($url->getUser());
    }

//    /**
//     * @return Url[] Returns an array of Url objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Url
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
