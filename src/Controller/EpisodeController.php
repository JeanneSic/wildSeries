<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\User;
use App\Form\EpisodeType;
use App\Repository\EpisodeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;

/**
 * @Route("/episode")
 */
class EpisodeController extends AbstractController
{
    /**
     * @Route("/", name="episode_index", methods={"GET"})
     * @param EpisodeRepository $episodeRepository
     * @return Response
     */
    public function index(EpisodeRepository $episodeRepository): Response
    {
        return $this->render('episode/index.html.twig', [
            'episodes' => $episodeRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="episode_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($episode);
            $entityManager->flush();

            return $this->redirectToRoute('episode_index');
        }

        return $this->render('episode/new.html.twig', [
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{episodeSlug}", name="episode_show", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTRIBUTOR")
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     * @param Episode $episode
     * @param Request $request
     * @return Response
     */
    public function show(Episode $episode, Request $request): Response
    {
        $episode = new Episode();
        $comments = $this->getDoctrine()->getRepository(Comment::class)
            ->findBy(['episode' => $episode]);

        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
//        $user = $this->getUser();
//        $comment->setAuthor($user->getUserName());

        var_dump($comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

//            $user = new User();
//            $user = $user->getUsername();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            //$entityManager->flush();

            //return new Response('Well Hi !' . $user->getUsername());
        }

        return $this->render('episode/show.html.twig', [
            'form' => $form->createView(),
            'episode' => $episode,
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/{episodeSlug}/edit", name="episode_edit", methods={"GET","POST"})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     * @param Request $request
     * @param Episode $episode
     * @return Response
     */
    public function edit(Request $request, Episode $episode): Response
    {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('episode_index');
        }

        return $this->render('episode/edit.html.twig', [
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{episodeSlug}", name="episode_delete", methods={"DELETE"})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     * @param Request $request
     * @param Episode $episode
     * @return Response
     */
    public function delete(Request $request, Episode $episode): Response
    {
        if ($this->isCsrfTokenValid('delete'.$episode->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($episode);
            $entityManager->flush();
        }

        return $this->redirectToRoute('episode_index');
    }
}
