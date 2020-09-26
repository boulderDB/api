<?php

namespace App\Controller;

use App\Components\Controller\ApiControllerTrait;
use App\Components\Controller\ContextualizedControllerTrait;
use App\Entity\BoulderLabel;
use App\Factory\RedisConnectionFactory;
use App\Form\BoulderLabelType;
use BlocBeta\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/label")
 */
class BoulderLabelController extends AbstractController
{
    use ApiControllerTrait;
    use ContextualizedControllerTrait;

    private $entityManager;
    private $contextService;
    private $redis;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route(methods={"GET"})
     */
    public function index()
    {
        $keys = $this->redis->keys(BoulderLabel::createKey(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            '*',
            '*'
        ));

        $labels = array_map(function ($key) {
            $label = BoulderLabel::fromKey($key);

            return $label->getTitle();
        }, $keys);

        $labels = array_unique($labels);

        return $this->json($labels);
    }

    /**
     * @Route(methods={"POST"})
     */
    public function create(Request $request)
    {
        $boulderLabel = new BoulderLabel();
        $boulderLabel->setUser($this->getUser());
        $boulderLabel->setLocation($this->contextService->getLocation());

        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(BoulderLabelType::class, $boulderLabel);
        $form->submit($data, false);

        if (!$form->isValid()) {
            return $this->json([
                "code" => Response::HTTP_BAD_REQUEST,
                "message" => $this->getFormErrors($form)
            ]);
        }

        $key = $boulderLabel->toKey();
        $this->redis->set($key, time());

        return $this->json(['key' => $key]);
    }

    /**
     * @Route("/boulder/{boulderId}/{title}", methods={"DELETE"})
     */
    public function remove(string $boulderId, string $title)
    {
        $key = BoulderLabel::createKey(
            $this->contextService->getLocation()->getId(),
            $this->getUser()->getId(),
            $boulderId,
            $title
        );

        $this->redis->del($key);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}