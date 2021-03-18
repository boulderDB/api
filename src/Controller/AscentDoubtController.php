<?php


namespace App\Controller;

use App\Entity\Ascent;
use App\Entity\AscentDoubt;
use App\Form\AscentDoubtType;
use App\Repository\AscentDoubtRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

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
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $doubts = $this->ascentDoubtRepository->getDoubts(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId()
        );

        $data = [];

        foreach ($doubts as &$doubt) {

            $data[] = [
                "id" => $doubt["id"],
                "author" => [
                    "id" => $doubt["author_id"],
                    "username" => $doubt["author_username"],
                    "message" => $doubt["doubt_description"],
                ],
                "ascent" => [
                    "type" => str_replace(Ascent::PENDING_DOUBT_FLAG, "", $doubt["ascent_type"])
                ],
                "boulder" => [
                    "id" => $doubt["boulder_id"],
                    "name" => $doubt["boulder_name"]
                ],
                "created_at" => $doubt["doubt_created_at"],
            ];

            $doubt["ascent_type"] = str_replace(Ascent::PENDING_DOUBT_FLAG, "", $doubt["ascent_type"]);
        }

        return $this->json($data);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
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
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, string $id)
    {
        /**
         * @var AscentDoubt $doubt
         */
        $doubt = $this->ascentDoubtRepository->find($id);

        if (!$doubt) {
            return $this->resourceNotFoundResponse("AscentDoubt", $id);
        }

        if (!$doubt) {
            return $this->unauthorizedResponse();
        }

        $form = $this->createFormBuilder($doubt, ["csrf_protection" => false])
            ->add("status", ChoiceType::class, [
                "choices" => [
                    AscentDoubt::STATUS_RESOLVED => AscentDoubt::STATUS_RESOLVED,
                    AscentDoubt::STATUS_READ => AscentDoubt::STATUS_READ,
                    AscentDoubt::STATUS_UNREAD => AscentDoubt::STATUS_UNREAD,
                    AscentDoubt::STATUS_UNRESOLVED => AscentDoubt::STATUS_UNRESOLVED,
                ],
                "constraints" => [
                    new NotBlank()
                ]
            ])
            ->getForm();

        $form->submit(self::decodePayLoad($request), false);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($doubt);
        $this->entityManager->flush();

        return $this->noContentResponse();
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

        return $this->okResponse($doubtCount);
    }
}
