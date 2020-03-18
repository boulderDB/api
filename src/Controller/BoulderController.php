<?php

namespace App\Controller;

use App\Components\Constants;
use App\Components\Controller\ApiControllerTrait;
use App\Entity\Boulder;
use App\Form\BoulderType;
use App\Repository\BoulderRepository;
use App\Service\ContextService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boulder")
 */
class BoulderController extends AbstractController
{
    use ApiControllerTrait;

    private $entityManager;
    private $contextService;
    private $boulderRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderRepository $boulderRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
    }

    /**
     * @Route("/{id}", methods={"GET"})
     */
    public function show(string $id)
    {
        if (!static::isValidId($id)) {
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => "Invalid id"
            ]);
        }

        $queryBuilder = $this->getBoulderQueryBuilder("
            partial ascent.{id, userId, type}, 
            partial ascent.{id, type, createdAt}, 
            partial user.{id,username,visible}"
        );

        $boulder = $queryBuilder
            ->leftJoin('boulder.ascents', 'ascent')
            ->leftJoin('ascent.user', 'user')
            ->where('boulder.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);

        $boulder['holdStyle'] = $boulder['color'];
        unset($boulder['color']);

        if (!isset($boulder['ascents'])) {
            $boulder['ascents'] = [];

            return $this->json($boulder);
        }

        $boulder['ascents'] = array_filter($boulder['ascents'], function ($ascent) {
            if (!in_array($ascent["type"], Constants::SCORED_ASCENT_TYPES)) {
                return false;
            }

            if (!$ascent["user"]["visible"]) {
                return false;
            }

            return true;
        });

        return $this->json($boulder);
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
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $this->getFormErrors($form)
            ]);
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->json([
            'id' => $boulder->getId()
        ]);
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        if (!static::isValidId($id)) {
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => "Invalid id"
            ]);
        }

        $boulder = $this->boulderRepository->find($id);

        if (!$boulder) {
            return $this->json([
                "code" => Response::HTTP_NO_CONTENT,
                "message" => "Boulder $id not found"
            ]);
        }

        $form = $this->createForm(BoulderType::class, $boulder);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $this->getFormErrors($form)
            ]);
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/filter/active", methods={"GET"})
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
            $boulder['holdStyle'] = $boulder['color'];
            unset($boulder['color']);

            return $boulder;
        }, $results);

        return $this->json($results);
    }

    private function getBoulderQueryBuilder(string $select = null)
    {
        $partials = "
                partial boulder.{id, name, createdAt, status, points}, 
                partial startWall.{id}, 
                partial endWall.{id}, 
                partial tag.{id}, 
                partial setter.{id},
                partial holdStyle.{id}, 
                partial grade.{id}
        ";

        if ($select) {
            $partials .= ", {$select}";
        }

        return $this->entityManager->createQueryBuilder()
            ->select($partials)
            ->from(Boulder::class, 'boulder')
            ->leftJoin('boulder.tags', 'tag')
            ->leftJoin('boulder.setters', 'setter')
            ->leftJoin('boulder.startWall', 'startWall')
            ->leftJoin('boulder.endWall', 'endWall')
            ->innerJoin('boulder.grade', 'grade')
            ->innerJoin('boulder.color', 'holdStyle');
    }
}
