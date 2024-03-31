<?php

namespace App\Controller;

use App\Entity\MailingList;
use App\Repository\MailingListRepository;
//use App\Service\EntityNormalizeService;
use App\Service\RecipientService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MailingListController extends JsonRpcController
{
//    public function __construct(
//        private readonly LoggerInterface        $logger,
//        private readonly EntityNormalizeService $normalizeService,
//        private readonly MailingListRepository  $repository
//    )
//    {
//    }
//
//    /**
//     * Точка входа для получения и сохранения данных писем
//     *
//     * @param Request $request
//     * @return JsonResponse
//     */
//    #[Route('/mailing', name: 'email_mailingList', methods: ['POST'])]
//    public function index(Request $request): JsonResponse
//    {
//        return parent::index($request);
//    }
//
//    public function get(int $letter, int $recipient)
//    {
//        try {
//            $result = $this->repository->findOneBy(['letter' => $letter, 'recipient' => $recipient]);
//        } catch (\Throwable $err) {
//            $ttt = $err;
//        }
//        return $result->toArray();
//    }
}