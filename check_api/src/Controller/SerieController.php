<?php

namespace App\Controller;

use App\Entity\Serie;
use App\Entity\UserSerie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


final class SerieController extends AbstractController
{
    // #[Route('/serie', name: 'app_serie')]
    // public function index(): Response
    // {
    //     return $this->render('serie/index.html.twig', [
    //         'controller_name' => 'SerieController',
    //     ]);
    // }

    // --------------- SHOW ALL SERIEs ---------------
    // #[Route("/", name: "series_index")]
    // public function index(EntityManagerInterface $em): Response
    // {
    //     $series = $em->getRepository(Serie::class)->findAll(); // récupérer toutes les séries
    //     return $this->render('serie/index.html.twig', [
    //         'title' => 'CheckSérieBox',
    //         'series' => $series
    //     ]);
    // }

    #[Route("/", name: "series_index")]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $userSeriesStatus = [];

        if ($user) {
            $userSeries = $em->getRepository(UserSerie::class)->findBy(['user' => $user]);
            foreach ($userSeries as $us) {
                $userSeriesStatus[$us->getSerie()->getId()] = [
                    'list' => $us->getList(), // "watched", "watching", "watchlist", "dropped"
                    'liked' => $us->isLiked() // true / false
                ];
            }
        }

        return $this->render('serie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'series' => $em->getRepository(Serie::class)->findAll(),
            'seriesRandom' => $em->getRepository(Serie::class)->findAllRandom(),
            'userSeriesStatus' => $userSeriesStatus,
            'types' => $em->getRepository(Serie::class)->findDistinctByField('type'),
            'countries' => $em->getRepository(Serie::class)->findDistinctByField('country'),
            'release_dates' => $em->getRepository(Serie::class)->findDistinctByField('release_date'),
            'platforms' => $em->getRepository(Serie::class)->findDistinctByField('platform'),
            'nb_seasons' => $em->getRepository(Serie::class)->findDistinctByField('nb_season'),
            'status' => $em->getRepository(Serie::class)->findDistinctByField('status')
        ]);
    }

    #[Route('/serie/{slug}', name: 'serie_show')]
    public function displaySerie(EntityManagerInterface $em, string $slug): Response
    {
        $serie = $em->getRepository(Serie::class)->findOneBy(["slug" => $slug]);

        if (!$serie) {
            throw $this->createNotFoundException('Série non trouvée');
        }

        return $this->render('serie/serie.html.twig', [
            'title' => 'CheckSérieBox',
            'serie' => $serie
        ]);
    }

    #[Route("/admin/", name: "admin_index")]
    public function admin(EntityManagerInterface $em): Response
    {
        $series = $em->getRepository(Serie::class)->findAll(); // récupérer toutes les séries
        $types = $em->getRepository(Serie::class)->findDistinctByField('type');
        $countries = $em->getRepository(Serie::class)->findDistinctByField('country');
        $release_dates = $em->getRepository(Serie::class)->findDistinctByField('platform');
        $platforms = $em->getRepository(Serie::class)->findDistinctByField('release_date');
        $nb_seasons = $em->getRepository(Serie::class)->findDistinctByField('nb_season');
        $status = $em->getRepository(Serie::class)->findDistinctByField('status');

        return $this->render('serie/admin.html.twig', [
            'title' => 'CheckSérieBox',
            'series' => $series,
            'types' => $types,
            'countries' => $countries,
            'release_dates' => $release_dates,
            'platforms' => $platforms,
            'nb_seasons' => $nb_seasons,
            'status' => $status,
        ]);
    }

    // --------------- ADD A SERIE ---------------
    #[Route("/admin/add", name: "serie_add")]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {

            // On crée une nouvelle série
            $data = $request->request;
            $serie = new Serie();

            $title = $data->get('title');
            $slug = $slugger
                ->slug($data->get('title'))
                ->lower();

            $serie->setTitle($title);
            $serie->setSlug($slug);
            $serie->setPosterUrl($data->get('poster_url'));
            $serie->setType($data->get('type'));
            $serie->setCountry($data->get('country'));
            $serie->setReleaseDate($data->get('release_date'));
            $serie->setPlatform($data->get('platform'));
            $serie->setNbSeason($data->get('nb_season'));
            $serie->setSynopsis($data->get('synopsis'));
            $serie->setStatus($data->get('status'));

            $em->persist($serie);
            $em->flush();

            return $this->redirectToRoute('series_index', [$this->addFlash('success', 'Votre série a bien été ajoutée !')]); // redirection vers la page principale avec message de succès
        }
        return $this->render('serie/add.html.twig', ['title' => 'CheckSérieBox / Add']); // afficher le formulaire
    }

    // --------------- EDIT A SERIE ---------------
    #[Route("/admin/edit/{id}", name: "serie_edit")]
    public function edit($id, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $serie = $em->getRepository(Serie::class)->find($id);
        if (!$serie) {
            throw $this->createNotFoundException('Serie non trouvée');
        }

        if ($request->isMethod('POST')) {

            $data = $request->request;

            $title = $data->get('title');
            $slug = $slugger
                ->slug($data->get('title'))
                ->lower();

            $serie->setTitle($title);
            $serie->setSlug($slug);
            $serie->setPosterUrl($data->get('poster_url'));
            $serie->setType($data->get('type'));
            $serie->setCountry($data->get('country'));
            $serie->setReleaseDate($data->get('release_date'));
            $serie->setPlatform($data->get('platform'));
            $serie->setNbSeason($data->get('nb_season'));
            $serie->setSynopsis($data->get('synopsis'));
            $serie->setStatus($data->get('status'));

            $em->flush(); // mise à jour en bdd

            return $this->redirectToRoute('series_index');
        }

        return $this->render('serie/edit.html.twig', [
            'title' => 'CheckSérieBox / Edit',
            'serie' => $serie
        ]);
    }

    // --------------- DELETE A SERIE ---------------
    #[Route("/admin/delete/{id}", name: "serie_delete")]
    public function delete($id, EntityManagerInterface $em): Response
    {
        $serie = $em->getRepository(Serie::class)->find($id);
        if ($serie) {
            $em->remove($serie);
            $em->flush();
        }
        return $this->redirectToRoute('series_index');
    }


    #[Route('/api/series', name: 'api_series', methods: ['GET'])]
    public function apiSeries(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $filters = [
            'type' => $request->query->get('type'),
            'country' => $request->query->get('country'),
            'release_date' => $request->query->get('release_date'),
            'platform' => $request->query->get('platform'),
            'nb_season' => $request->query->get('nb_season'),
            'status' => $request->query->get('status'),
        ];

        $series = $em->getRepository(Serie::class)->findByFilters($filters);

        return $this->json($series);
    }

}
