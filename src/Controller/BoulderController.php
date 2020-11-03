<?php

namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\Label;
use App\Entity\Grade;
use App\Factory\RedisConnectionFactory;
use App\Form\BoulderType;
use App\Form\MassOperationType;
use App\Repository\BoulderRepository;
use App\Controller\ContextualizedControllerTrait;
use App\Controller\FormErrorTrait;
use App\Controller\RateLimiterTrait;
use App\Controller\RequestTrait;
use App\Controller\ResponseTrait;
use App\Service\ContextService;
use App\Service\Serializer;
use App\Service\SerializerInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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
        /**
         * @var Boulder $boulder
         */
        $boulder = $this->boulderRepository
            ->createQueryBuilder("boulder")
            ->innerJoin("boulder.holdType", "holdType")
            ->innerJoin("boulder.grade", "grade")
            ->innerJoin("boulder.startWall", "startWall")
            ->leftJoin("boulder.endWall", "endWall")
            ->leftJoin("boulder.setters", "setter")
            ->leftJoin("boulder.tags", "tag")
            ->where("boulder.id = :id")
            ->setParameter("id", $id)
            ->getQuery()
            ->setFetchMode(Grade::class, "grade", ClassMetadataInfo::FETCH_EAGER)
            ->getOneOrNullResult();

        if (!$boulder) {
            return $this->resourceNotFoundResponse("Boulder", $id);
        }

        return $this->okResponse(Serializer::serialize($boulder, [SerializerInterface::GROUP_DETAIL]));
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
        $builder = $this->getBoulderQueryBuilder();

        $results = $builder->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->getQuery()
            ->getArrayResult();

        $isAdmin = $this->isLocationAdmin();

        $results = array_map(function ($boulder) use ($isAdmin) {
            $boulder['labels'] = $this->getLabels($boulder['id']);

            if (!$isAdmin) {
                unset($boulder["internal_grade"]);
            }

            return $boulder;
        }, $results);

        return $this->json($results);
    }

    /**
     * @Route("/count", methods={"GET"})
     */
    public function count()
    {
        $builder = $this->boulderRepository->createQueryBuilder("boulder");

        $count = $builder
            ->select("count(boulder.id)")
            ->where("boulder.location = :location")
            ->andWhere("boulder.status = :status")
            ->setParameter("location", $this->contextService->getLocation()->getId())
            ->setParameter("status", Boulder::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleResult();

        return $this->okResponse($count ? $count[1] : 0);
    }

    /**
     * @Route("", methods={"POST"})
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
                $boulder->clearAscents();
            }

            $this->entityManager->persist($boulder);
        }

        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function filterAscents(array $ascents): array
    {
        $types = [
            Ascent::ASCENT_FLASH,
            Ascent::ASCENT_TOP,
            Ascent::ASCENT_FLASH . Ascent::PENDING_DOUBT_FLAG,
            Ascent::ASCENT_TOP . Ascent::PENDING_DOUBT_FLAG,
        ];

        $ascents = array_filter($ascents, function ($ascent) use ($types) {
            if (!in_array($ascent["type"], $types)) {
                return false;
            }

            if (!$ascent["user"]["visible"]) {
                return false;
            }

            return true;
        });

        return array_values($ascents);
    }

    private function getLabels(string $id)
    {
        //$key = BoulderLabel::createKey(
        //    $this->contextService->getLocation()->getId(),
        //    $this->getUser()->getId(),
        //    $id,
        //    "*"
        //);
        //
        //return array_map(function ($key) {
        //    $label = BoulderLabel::fromKey($key);
        //
        //    return $label->getTitle();
        //}, $this->redis->keys($key));
    }

    private function getBoulderQueryBuilder(string $select = null): QueryBuilder
    {
        $partials = "
                partial boulder.{id, name, createdAt, status, points}, 
                partial startWall.{id}, 
                partial endWall.{id}, 
                partial tag.{id}, 
                partial setter.{id},
                partial holdType.{id}, 
                partial grade.{id},
                partial internalGrade.{id}
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
            ->leftJoin('boulder.internalGrade', 'internalGrade')
            ->innerJoin('boulder.holdType', 'holdType');
    }
}
