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

        if (!isset($data['serieId'], $data['list'])) {
            return new JsonResponse(['error' => 'Invalid data'], 400);
        }

        $serie = $em->getRepository(Serie::class)->find($data['serieId']);

        if (!$serie) {
            return new JsonResponse(['error' => 'Serie not found'], 404);
        }

        $repo = $em->getRepository(UserSerie::class);
        $userSerie = $repo->findOneBy([
            'user' => $user,
            'serie' => $serie,
        ]);

        if (!$userSerie) {
            $userSerie = new UserSerie();
            $userSerie->setUser($user);
            $userSerie->setSerie($serie);
            $userSerie->setAddedAt(new \DateTimeImmutable());
            $userSerie->setLiked(false); // IMPORTANT
            $em->persist($userSerie);
        }

        // ====== LIKE ======
        if ($data['list'] === 'like') {

            $currentLike = $userSerie->isLiked() ?? false;
            $userSerie->setLiked(!$currentLike);
            $em->flush();

            return new JsonResponse([
                'liked' => $userSerie->isLiked()
            ]);
        }

        // ====== LISTES ======
        if ($userSerie->getList() === $data['list']) {
            $userSerie->setList(null);
        } else {
            $userSerie->setList($data['list']);
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
            'watched' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'watched'),
            'watching' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'watching'),
            // 'a_suivre' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'À suivre'),
            'watchlist' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'watchlist'),
            'dropped' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'dropped'),
            'liked' => $em->getRepository(UserSerie::class)->findLikedByUser($user),
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
            'watching' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'watching'),
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

    #[Route("/username/dropped", name: "serie_user_dropped_list")]
    public function dropped_list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('userserie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'dropped' => $em->getRepository(UserSerie::class)->findByUserAndList($user, $user, 'Abandonnée'),
            // 'like' => $em->getRepository(UserSerie::class)->findByUserAndList($user, 'Like'),
        ]);
    }

}
