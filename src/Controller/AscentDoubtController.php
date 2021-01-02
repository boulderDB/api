<?php


namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Form\AscentDoubtType;
use App\Repository\AscentDoubtRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/doubt")
 */
class AscentDoubtController extends AbstractController
{
    use FormErrorTrait;
    use RequestTrait;
    use ResponseTrait;

    private ContextService $contextService;
    private AscentDoubtRepository $ascentDoubtRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ContextService $contextService,
        AscentDoubtRepository $ascentDoubtRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->contextService = $contextService;
        $this->ascentDoubtRepository = $ascentDoubtRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route(methods={"POST"})
     */
    public function doubt(Request $request)
    {
        $ascentDoubt = new AscentDoubt();
        $ascentDoubt->setAuthor($this->getUser());

        $form = $this->createForm(AscentDoubtType::class, $ascentDoubt);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        /**
         * @var Ascent $ascent
         */
        $ascent = $form->getData()->getAscent();
        $ascent->setDoubted();

        $ascentDoubt->setRecipient($ascent->getUser());
        $ascentDoubt->setBoulder($ascent->getBoulder());

        $this->entityManager->persist($ascent);
        $this->entityManager->persist($ascentDoubt);

        $this->entityManager->flush();

        return $this->createdResponse($ascentDoubt);
    }

    /**
     * @Route("/{id}/resolve", methods={"PUT"})
     */
    public function resolve(string $id)
    {

    }

    /**
     * @Route("/unread", methods={"GET"})
     */
    public function unread()
    {
        $doubts = $this->ascentDoubtRepository->getDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            AscentDoubt::STATUS_UNREAD
        );

        return $this->json($doubts);
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index(Request $request)
    {
        $doubts = $this->ascentDoubtRepository->getDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            AscentDoubt::STATUS_UNRESOLVED
        );

        return $this->json($doubts);
    }

    /**
     * @Route("/count", methods={"GET"})
     */
    public function count()
    {
        $doubtCount = $this->ascentDoubtRepository->countDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId()
        );

        return $this->okResponse($doubtCount ? $doubtCount[1] : 0);
    }
}
