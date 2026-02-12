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


    #[Route("/username", name: "serie_user_index")]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('userserie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'user' => $user->getUserIdentifier(),
            'vu' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Vu'),
            'en_cours' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'En cours'),
            // 'a_suivre' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'À suivre'),
            'watchlist' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Watchlist'),
            'abandonnee' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Abandonnée'),
            'likes' => $em->getRepository(UserSerie::class)->findLikedByUser($user),
        ]);
    }

    #[Route("/username/series", name: "serie_user_series_list")]
    public function series_list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('userserie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'vu' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Vu'),
            // 'like' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Like'),
        ]);
    }

    #[Route("/username/watching", name: "serie_user_watching_list")]
    public function watching_list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('userserie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'en_cours' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'En cours'),
            // 'like' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Like'),
        ]);
    }

    #[Route("/username/watchlist", name: "serie_user_watchlist")]
    public function watchlist(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('userserie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'watchlist' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Watchlist'),
            // 'like' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Like'),
        ]);
    }

    #[Route("/username/abandon", name: "serie_user_abandon_list")]
    public function abandon_list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('userserie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'abandonnee' => $em->getRepository(UserSerie::class)->findByUserAndList($user, $user, 'Abandonnée'),
            // 'like' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Like'),
        ]);
    }

}
