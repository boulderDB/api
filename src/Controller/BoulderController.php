<?php

namespace App\Controller;

use App\Components\Constants;
use App\Components\Controller\ApiControllerTrait;
use App\Components\Controller\ContextualizedControllerTrait;
use App\Components\Scoring\ScoringInterface;
use App\Entity\Boulder;
use App\Entity\BoulderLabel;
use App\Factory\RedisConnectionFactory;
use App\Form\BoulderType;
use App\Form\MassOperationType;
use App\Repository\BoulderRepository;
use App\Service\ContextService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
        $queryBuilder = $this->getBoulderQueryBuilder("
            partial ascent.{id, userId, type, createdAt}, 
            partial user.{id, username, visible}"
        );

        /**
         * @var Boulder $boulder
         */
        try {
            $boulder = $queryBuilder
                ->leftJoin('boulder.ascents', 'ascent')
                ->leftJoin('ascent.user', 'user')
                ->where('boulder.id = :id')
                ->setParameter('id', (int)$id)
                ->getQuery()
                ->getSingleResult(AbstractQuery::HYDRATE_ARRAY);
        } catch (NoResultException $e) {
            return $this->notFound("Boulder", $id);
        } catch (NonUniqueResultException $e) {
            return $this->internalError();
        }

        $boulder = self::replaceLegacyNames($boulder);
        $boulder['ascents'] = $this->filterAscents($boulder['ascents']);
        $boulder['labels'] = $this->getLabels($id);

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
            return $this->badRequest($this->getFormErrors($form));
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->created($boulder->getId());
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        $boulder = $this->boulderRepository->find($id);

        if (!$boulder) {
            return $this->notFound("Boulder", $id);
        }

        $form = $this->createForm(BoulderType::class, $boulder);
        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return $this->badRequest($this->getFormErrors($form));
        }

        $this->entityManager->persist($boulder);
        $this->entityManager->flush();

        return $this->noContent();
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $builder = $this->getBoulderQueryBuilder();

        $results = $builder->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->setParameter('location', $this->contextService->getLocation()->getId())
            ->setParameter('status', Constants::STATUS_ACTIVE)
            ->getQuery()
            ->getArrayResult();

        $results = array_map(function ($boulder) {
            $boulder['labels'] = $this->getLabels($boulder['id']);

            return self::replaceLegacyNames($boulder);
        }, $results);

        return $this->json($results);
    }

    private function filterAscents(array $ascents)
    {
        return array_filter($ascents, function ($ascent) {
            if (!in_array($ascent["type"], ScoringInterface::SCORED_ASCENT_TYPES)) {
                return false;
            }

            if (!$ascent["user"]["visible"]) {
                return false;
            }

            return true;
        });
    }

    private function getLabels(string $id)
    {
        $key = BoulderLabel::createKey(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            $id,
            "*"
        );

        return array_map(function ($key) {
            $label = BoulderLabel::fromKey($key);

            return $label->getTitle();
        }, $this->redis->keys($key));
    }

    private static function replaceLegacyNames(array $boulder)
    {
        $boulder['createdAt'] = $boulder['createdAt']->format('c');

        $boulder['holdStyle'] = $boulder['color'];
        unset($boulder['color']);

        if (!$boulder['endWall']) {
            $boulder['endWall'] = $boulder['startWall'];
        }

        return $boulder;
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
                $boulder->clearAscents();
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
