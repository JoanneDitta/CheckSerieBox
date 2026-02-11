<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Entity\UserSerie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserSerieController extends AbstractController
{

    // #[Route("/username", name: "serie_user_index")]
    // public function index(EntityManagerInterface $em): Response
    // {
    //     $user = $this->getUser();

    //     if (!$user) {
    //         throw $this->createAccessDeniedException();
    //     }

    //     $vu = $em->getRepository(UserSerie::class)->findAllChronologicaly('Vu');
    //     $en_cours = $em->getRepository(UserSerie::class)->findAllChronologicaly('En cours');
    //     $a_suivre = $em->getRepository(UserSerie::class)->findAllChronologicaly('À suivre');
    //     $watchlist = $em->getRepository(UserSerie::class)->findAllChronologicaly('Watchlist');
    //     $abandonnee = $em->getRepository(UserSerie::class)->findAllChronologicaly('Abandonnée');
    //     $like = $em->getRepository(UserSerie::class)->findAllChronologicaly('Like');

    //     return $this->render('userserie/index.html.twig', [
    //         'title' => 'CheckSérieBox',
    //         'vu' => $vu,
    //         'en_cours' => $en_cours,
    //         'a_suivre' => $a_suivre,
    //         'watchlist' => $watchlist,
    //         'abandonnee' => $abandonnee,
    //         'like' => $like,
    //     ]);
    // }

    #[Route('/api/user/serie/toggle', name: 'user_serie_toggle', methods: ['POST'])]
    public function toggle(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $serieId = $data['serieId'] ?? null;
        $list    = $data['list'] ?? null;

        if (!$serieId || !$list) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $serie = $em->getRepository(Serie::class)->find($serieId);

        if (!$serie) {
            return new JsonResponse(['error' => 'Serie not found'], 404);
        }

        $repo = $em->getRepository(UserSerie::class);
        $userSerie = $repo->findOneByUserAndSerie($user, $serie);

        // LIKE = cas à part
        if ($list === 'like') {
            if (!$userSerie) {
                $userSerie = new UserSerie();
                $userSerie->setUser($user);
                $userSerie->setSerie($serie);
                $userSerie->setAddedAt(new \DateTimeImmutable());
                $em->persist($userSerie);
            }

            $userSerie->setLiked(!$userSerie->isLiked());
            $em->flush();

            return new JsonResponse([
                'liked' => $userSerie->isLiked()
            ]);
        }

        // LISTES PRINCIPALES
        if (!$userSerie) {
            $userSerie = new UserSerie();
            $userSerie->setUser($user);
            $userSerie->setSerie($serie);
            $userSerie->setAddedAt(new \DateTimeImmutable());
            $em->persist($userSerie);
        }

        // toggle
        if ($userSerie->getList() === $list) {
            $userSerie->setList(null);
        } else {
            $userSerie->setList($list);
        }

        $em->flush();

        return new JsonResponse([
            'list' => $userSerie->getList()
        ]);
    }


}
