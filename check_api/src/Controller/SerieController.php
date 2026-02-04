<?php

namespace App\Controller;

use App\Entity\Serie;
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
        $series = $em->getRepository(Serie::class)->findAll(); // récupérer toutes les séries
        $seriesRandomLimited = $em->getRepository(Serie::class)->findAllRandom(); // récupérer toutes les séries dans un ordre random
        $types = $em->getRepository(Serie::class)->findDistinctTypes();
        $countries = $em->getRepository(Serie::class)->findDistinctCountries();
        $release_dates = $em->getRepository(Serie::class)->findDistinctRelease_dates();
        $platforms = $em->getRepository(Serie::class)->findDistinctPlatforms();
        $nb_seasons = $em->getRepository(Serie::class)->findDistinctNb_seasons();
        $status = $em->getRepository(Serie::class)->findDistinctStatuts();

        return $this->render('serie/index.html.twig', [
            'title' => 'CheckSérieBox',
            'series' => $series,
            'seriesRandomLimited' => $seriesRandomLimited,
            'types' => $types,
            'countries' => $countries,
            'release_dates' => $release_dates,
            'platforms' => $platforms,
            'nb_seasons' => $nb_seasons,
            'status' => $status,
        ]);
    }

    #[Route('/serie/{slug}', name: 'serie_show')]
    public function display_a_serie(EntityManagerInterface $em, string $slug): Response
    {
        $serie = $em->getRepository(Serie::class)->findOneBy(["slug" => $slug]); // récupérer 1 série via son slug

    if (!$serie) {
        throw $this->createNotFoundException('Série non trouvée');
    }

        return $this->render('serie/serie.html.twig', [
            'title' => 'CheckSérieBox',
            'serie' => $serie
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
            $serie->setPosterUrl($data->get('posterUrl'));
            $serie->setType($data->get('type'));
            $serie->setCountry($data->get('country'));
            $serie->setReleaseDate($data->get('releaseDate'));
            $serie->setPlatform($data->get('platform'));
            $serie->setNbSeason($data->get('nbSeason'));
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
    public function edit($id, Request $request, EntityManagerInterface $em): Response
    {
        $serie = $em->getRepository(Serie::class)->find($id);
        if (!$serie) {
            throw $this->createNotFoundException('Serie non trouvée');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request;
            $serie->setTitle($data->get('title'));
            $serie->setPosterUrl($data->get('posterUrl'));
            $serie->setType($data->get('type'));
            $serie->setCountry($data->get('country'));
            $serie->setReleaseDate($data->get('releaseDate'));
            $serie->setPlatform($data->get('platform'));
            $serie->setNbSeason($data->get('nbSeason'));
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
}
