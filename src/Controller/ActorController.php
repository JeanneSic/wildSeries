<?php


namespace App\Controller;

use App\Entity\Actor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actor", name = "actor_")
 */
class ActorController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return Response
     */
    public function index(): Response
    {
        $actors = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findAll();

        return $this->render("actor/index.html.twig", ["actors" => $actors]);
    }

    /**
     * @Route("/{id}", requirements={"id"="^\d+$"}, methods={"GET"}, name="show")
     * @param Actor $actor
     * @return Response A response instance
     */
    public function show(Actor $actor): Response
    {
        if (!$actor) {
            throw $this->createNotFoundException('No actor with id : ' . $actor->getId() . ' found');
        }

        return $this->render("actor/show.html.twig", [
            'actor' => $actor
        ]);
    }
}
