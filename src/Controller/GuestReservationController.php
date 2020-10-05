<?php

namespace App\Controller;

use App\Controller\RequestTrait;
use App\Controller\ResponseTrait;
use App\Factory\RedisConnectionFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GuestReservationController extends AbstractController
{
    use ResponseTrait;
    use RequestTrait;

    private \Redis $redis;

    public function __construct()
    {
        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("/reservation/cancel/{hash}", methods={"put"})
     */
    public function cancel(string $hash)
    {
        $this->redis->exists($hash);
    }

    /**
     * @Route("/reservation/guest", methods={"post"})
     */
    public function createGuest()
    {

    }
}