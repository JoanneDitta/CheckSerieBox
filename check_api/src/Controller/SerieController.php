<?php

namespace App\Controller;

use App\Entity\Serie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SerieController extends AbstractController
{
    // #[Route('/serie', name: 'app_serie')]
    // public function index(): Response
    // {
    //     return $this->render('serie/index.html.twig', [
    //         'controller_name' => 'SerieController',
    //     ]);
    // }

    // --------------- SHAOW ALL SERIEs ---------------
    #[Route("/", name: "series_index")]
    public function index(EntityManagerInterface $em): Response
    {
        $series = $em->getRepository(Serie::class)->findAll(); // récupérer toutes les séries
        return $this->render('series/index.html.twig', [
            'title' => 'CheckSérieBox',
            'series' => $series
        ]);
    }

    // --------------- ADD A SERIE ---------------
    #[Route("/add", name: "serie_add")]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            // On crée une nouvelle série
            $data = $request->request;
            $serie = new Serie();
            $serie->setTitle($data->get('title'));
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
        return $this->render('series/add.html.twig', ['title' => 'CheckSérieBox / Add']); // afficher le formulaire
    }

    // --------------- EDIT A SERIE ---------------
    #[Route("/edit/{id}", name: "serie_edit")]
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

        return $this->render('series/edit.html.twig', [
            'title' => 'CheckSérieBox / Edit',
            'serie' => $serie
        ]);
    }

    // --------------- DELETE A SERIE ---------------
    #[Route("/delete/{id}", name: "serie_delete")]
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
