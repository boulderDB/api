<?php

namespace App\Controller;

use App\Components\Constants;
use App\Components\Controller\ApiControllerTrait;
use App\Components\Controller\ContextualizedControllerTrait;
use App\Entity\Boulder;
use App\Entity\BoulderError;
use App\Factory\RedisConnectionFactory;
use App\Factory\ResponseFactory;
use App\Form\BoulderErrorType;
use App\Form\BoulderLabelType;
use App\Form\BoulderType;
use App\Form\MassOperationType;
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
    use ContextualizedControllerTrait;

    private $entityManager;
    private $contextService;
    private $boulderRepository;
    private $redis;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        BoulderRepository $boulderRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->boulderRepository = $boulderRepository;
        $this->redis = RedisConnectionFactory::create();
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
        $this->denyUnlessLocationAdmin();

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
        $this->denyUnlessLocationAdmin();

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

        $results = $builder->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', 'active')
            ->getQuery()
            ->getArrayResult();

        $results = array_map(function ($boulder) {
            $boulder['createdAt'] = $boulder['createdAt']->format('c');
            $boulder['holdStyle'] = $boulder['color'];
            unset($boulder['color']);

            if (!$boulder['endWall']) {
                $boulder['endWall'] = $boulder['startWall'];
            }

            return $boulder;
        }, $results);

        return $this->json($results);
    }

    /**
     * @Route("/{id}/error", methods={"POST"})
     */
    public function createError(Request $request, string $id)
    {
        $boulderError = new BoulderError();
        $boulderError->setAuthor($this->getUser());

        $form = $this->createForm(BoulderErrorType::class, $boulderError);

        $data = json_decode($request->getContent());
        $data['boulder'] = $id;

        $form->submit($data, true);

        if (!$form->isValid()) {
            return $this->json($this->getFormErrors($form));
        }

        $this->entityManager->persist($boulderError);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}/label", methods={"POST"})
     */
    public function addLabel(string $id, Request $request)
    {
        $form = $this->createForm(BoulderLabelType::class);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $this->getFormErrors($form)
            ]);
        }

        /**
         * @var Boulder $boulder
         */
        $boulder = $form->getData()['boulder'];
        $label = $form->getData()['label'];

        $key = "user:{$this->getUser()->getId()}:boulder:{$boulder->getId()}:label:{$label}";
        $this->redis->set($key, time());

        return $this->json(['key' => $key]);
    }

    /**
     * @Route("/{id}/label/{label}", methods={"DELETE"})
     */
    public function removeLabel(string $id, string $label)
    {
        $key = "user:{$this->getUser()->getId()}:boulder:{$id}:label:{$label}";
        $this->redis->del($key);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/mass", methods={"PUT"})
     */
    public function massOperation(Request $request)
    {
        $form = $this->createForm(MassOperationType::class);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $this->getFormErrors($form)
            ]);
        }

        /**
         * @var Boulder $boulder
         */
        foreach ($form->getData()["items"] as $boulder) {
            if ($form->getData()["operation"] === MassOperationType::OPERATION_DEACTIVATE) {
                $boulder->setStatus(Boulder::STATUS_INACTIVE);
            }

            if ($form->getData()["operation"] === MassOperationType::OPERATION_PRUNE_ASCENTS) {
                // todo: implement prune ascents
            }

            $this->entityManager->persist($boulder);
        }

        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
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
