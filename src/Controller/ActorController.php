<?php


namespace App\Controller;

use App\Entity\Actor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("/{actorSlug}", methods={"GET"}, name="show")
     * @ParamConverter("actor", class="App\Entity\Actor", options={"mapping": {"actorSlug": "slug"}})
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
