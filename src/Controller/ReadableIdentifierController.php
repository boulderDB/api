<?php

namespace App\Controller;

use App\Entity\ReadableIdentifier;
use App\Form\ReadableIdentifierType;
use App\Repository\ReadableIdentifierRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Color\Color;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

/**
 * @Route("/readable-identifiers")
 */
class ReadableIdentifierController extends AbstractController
{
    use ResponseTrait;
    use CrudTrait;
    use ContextualizedControllerTrait;

    private ReadableIdentifierRepository $readableIdentifierRepository;
    private ContextService $contextService;
    private EntityManagerInterface $entityManager;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ReadableIdentifierRepository $readableIdentifierRepository,
        ContextService $contextService,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameterBag
    )
    {
        $this->readableIdentifierRepository = $readableIdentifierRepository;
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route(methods={"GET"}, name="readable_identifier_index")
     */
    public function index()
    {
        $this->denyUnlessLocationAdmin();

        return $this->okResponse(
            $this->readableIdentifierRepository->getUnassigned($this->contextService->getLocation()->getId())
        );
    }

    /**
     * @Route(methods={"POST"}, name="readable_identifier_create")
     */
    public function create(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        return $this->createEntity($request, ReadableIdentifier::class, ReadableIdentifierType::class);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="readable_identifier_read", requirements={"id": "\d+"}, methods={"GET"})
     */
    public function read(int $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->readEntity(ReadableIdentifier::class, $id, ["detail"]);
    }

    /**
     * @Route("/{id}", methods={"GET"}, name="readable_identifier_read_by_value", methods={"GET"})
     */
    public function readByValue(string $id)
    {
        $match = $this->readableIdentifierRepository->findOneBy(["value" => $id]);

        if (!$match) {
            return $this->resourceNotFoundResponse(ReadableIdentifier::RESOURCE_NAME, $id);
        }

        return $this->okResponse($match);
    }

    /**
     * @Route("/{id}", methods={"PUT"}, name="readable_identifier_update")
     */
    public function update(Request $request, string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->updateEntity($request, ReadableIdentifier::class, ReadableIdentifierType::class, $id);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, name="readable_identifier_delete")
     */
    public function delete(string $id)
    {
        $this->denyUnlessLocationAdmin();

        return $this->deleteEntity(ReadableIdentifier::class, $id);
    }

    /**
     * @Route("/{identifier}/code", methods={"GET"}, name="readable_identifier_code")
     */
    public function code(string $identifier)
    {
        $value = $_ENV["CLIENT_HOSTNAME"] . "/" . $this->contextService->getLocation()->getUrl() . "/boulder/" . $identifier;

        $code = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($value)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->logoPath($this->parameterBag->get('kernel.project_dir') . "/public/logo.png")
            ->logoResizeToWidth(150)
            ->labelText($identifier)
            ->labelFont(new NotoSans(12))
            ->labelAlignment(new LabelAlignmentCenter())
            ->foregroundColor(new Color(0, 0, 0))
            ->build();

        return new Response($code->getString(), Response::HTTP_OK, [
            'Content-type' => $code->getMimeType()
        ]);
    }
}