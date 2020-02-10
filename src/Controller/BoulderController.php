<?php

namespace App\Controller;

use App\Components\Constants;
use App\Components\Controller\ApiControllerTrait;
use App\Entity\Boulder;
use App\Form\BoulderType;
use App\Service\ContextService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulder")
 */
class BoulderController extends AbstractController
{
    use ApiControllerTrait;

    private $entityManager;
    private $contextService;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
    }

    /**
     * @Route("/{id}")
     */
    public function show(string $id)
    {
        $queryBuilder = $this->getBoulderQueryBuilder("
        partial ascent.{id, userId, type}, 
        partial ascent.{id, type, createdAt}, 
        partial user.{id,username}");

        $boulder = $queryBuilder
            ->leftJoin('boulder.ascents', 'ascent')
            ->leftJoin('ascent.user', 'user')
            ->where('boulder.id = :id')
            ->andWhere('user.visible = :visible')
            ->setParameter('id', $id)
            ->setParameter('visible', true)
            ->getQuery()
            ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);

        return $this->json($boulder);
    }

    /**
     * @Route("/filter/active")
     */
    public function active()
    {
        $builder = $this->getBoulderQueryBuilder();

        $results = $builder->where('boulder.tenant = :tenant')
            ->andWhere('boulder.status = :status')
            ->setParameter('tenant', $this->contextService->getLocation()->getId())
            ->setParameter('status', 'active')
            ->getQuery()
            ->getArrayResult();

        $results = array_map(function ($boulder) {
            $boulder['createdAt'] = $boulder['createdAt']->format('c');

            return $boulder;
        }, $results);

        return $this->json($results);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyAccessUnlessGranted(Constants::ROLE_ADMIN);

        $boulder = new Boulder();

        $form = $this->createForm(BoulderType::class, $boulder);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->json([
            'id' => $boulder->getId()
        ]);
    }

    private function getBoulderQueryBuilder(string $select = null)
    {
        return $this->entityManager->createQueryBuilder()
            ->select("
                partial boulder.{id, name, createdAt, status}, 
                partial startWall.{id}, 
                partial endWall.{id}, 
                partial tag.{id}, 
                partial setter.{id},
                partial color.{id}, 
                partial grade.{id},
                {$select}
            ")
            ->from(Boulder::class, 'boulder')
            ->leftJoin('boulder.tags', 'tag')
            ->leftJoin('boulder.setters', 'setter')
            ->leftJoin('boulder.startWall', 'startWall')
            ->leftJoin('boulder.endWall', 'endWall')
            ->innerJoin('boulder.grade', 'grade')
            ->innerJoin('boulder.color', 'color');
    }
}
