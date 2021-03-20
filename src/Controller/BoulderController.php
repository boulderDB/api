<?php

namespace App\Controller;

use App\Entity\Boulder;
use App\Factory\RedisConnectionFactory;
use App\Form\BoulderType;
use App\Form\MassOperationType;
use App\Repository\BoulderRepository;
use App\Service\CacheService;
use App\Service\ContextService;
use App\Service\Serializer;
use App\Service\SerializerInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
    use FormErrorTrait;
    use ResponseTrait;
    use RequestTrait;
    use RateLimiterTrait;
    use ContextualizedControllerTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private BoulderRepository $boulderRepository;
    private \Redis $redis;

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
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"GET"})
     */
    public function show(string $id)
    {
        $boulder = $this->boulderRepository->getOne($id);

        if (!$boulder) {
            return $this->resourceNotFoundResponse("Boulder", $id);
        }

        return $this->okResponse(Serializer::serialize(
            $boulder,
            [
                SerializerInterface::GROUP_DETAIL,
                $this->isLocationAdmin() ? SerializerInterface::GROUP_ADMIN : null
            ]
        ));
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $boulder = new Boulder();

        $form = $this->createForm(BoulderType::class, $boulder);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->createdResponse($boulder);
    }

    /**
     * @Route("/{id}", requirements={"id": "\d+"}, methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();
        $boulder = $this->boulderRepository->find($id);

        if (!$boulder) {
            return $this->resourceNotFoundResponse("Boulder", $id);
        }

        $form = $this->createForm(BoulderType::class, $boulder);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function index()
    {
        $boulders = $this->boulderRepository->getAll(
            $this->contextService->getLocation()->getId(),
            $this->isLocationAdmin()
        );

        $data = array_map(function ($boulder) {
            $data = [
                "id" => $boulder["id"],
                "name" => $boulder["name"],
                "start_wall" => $boulder["startWall"]["id"],
                "end_wall" => $boulder["endWall"] ? $boulder["endWall"]["id"] : null,
                "hold_type" => $boulder["holdType"]["id"],
                "grade" => $boulder["grade"]["id"],
                "setters" => array_map(function ($setter) {
                    return $setter["id"];
                }, $boulder["setters"]),
                "created_at" => Serializer::formatDate($boulder["createdAt"]),
            ];

            if ($this->isLocationAdmin()) {
                $data["internal_grade"] = $boulder["internalGrade"]["id"];
            }

            return $data;
        }, $boulders);

        return $this->okResponse($data);
    }

    /**
     * @Route("/count", methods={"GET"})
     */
    public function count()
    {
        $count = $this->boulderRepository->countActive(
            $this->contextService->getLocation()->getId()
        );

        return $this->okResponse($count);
    }

    /**
     * @Route("/mass", methods={"PUT"})
     */
    public function mass(Request $request)
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
                $boulder->setAscents(new ArrayCollection());
            }

            $this->entityManager->persist($boulder);
        }

        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
