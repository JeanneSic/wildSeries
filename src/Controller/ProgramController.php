<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Service\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
/**
 * @Route("/programs", name = "program_")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(SessionInterface $session): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render('programs/index.html.twig', [
            'programs' => $programs
        ]);
    }

    /**
     * @Route("/new", name = "new")
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     */
    public function new(Request $request, Slugify $slugify): Response
    {
        $program = new Program();

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($program);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Le nouveau programme a bien été crée'
            );

            return $this->redirectToRoute('program_index');
        }

        return $this->render('programs/new.html.twig', ["form" => $form->createView()]);
    }


    /**
     * Getting a program by id
     *
     * @Route("/show/{programSlug}", methods={"GET"}, name="show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @param Program $program
     * @return Response
     */
    public function show(Program $program): Response
    {
        if (!$program) {
            throw $this->createNotFoundException('No program with id : '. $program->getId() .' found in program\'s table.');
        }

        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program->getId()],
                ['id' => 'ASC']
            );

        return $this->render('programs/show.html.twig', ['program' => $program, 'seasons' => $seasons]);
    }

    /**
     * @Route("/{programSlug}/seasons/{seasonId}", methods={"GET"}, name = "season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @param Program $program
     * @param Season $season
     * @return Response
     */
    public function showSeason(Program $program, Season $season)
    {
        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season' => $season->getId()],
                ['id' => 'ASC']
            );

        return $this->render('programs/season_show.html.twig', ['program'=> $program, 'season'=> $season, 'episodes' => $episodes]);
    }

    /**
     * @Route("/{programSlug}/seasons/{seasonId}/episodes/{episodeSlug}", methods={"GET", "POST"}, name = "episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     * @param Request $request
     * @return Response
     */
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request)
    {
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['episode' => $episode, 'id' => 'DESC']);

        $comment = new Comment();
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);

        if ($formComment->isSubmitted() && $formComment->isValid()) {
            $user = $this->getUser();
            $comment->setAuthor($user);
            $comment->setEpisode($episode);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->render('programs/episode_show.html.twig',
            ['program'=> $program,
                'season'=> $season,
                'episode' => $episode,
                'comments' => $comments,
                'formComment' => $formComment->createView()]);
    }

    /**
     * @Route("/{programSlug}/edit", name="edit", methods={"GET","POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @param Request $request
     * @param Program $program
     * @return Response
     */
    public function edit(Request $request, Program $program): Response
    {
        // Check wether the logged in user is the owner of the program
        if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Seul le créateur du programme peut le modifier!');
        }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash(
                'warning',
                'Le programme a bien été modifié'
            );
            return $this->redirectToRoute('program_index');
        }

        return $this->render('programs/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{programSlug}/watchlist", name="watchlist", methods={"GET", "POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @param Request $request
     * @param Program $program
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function addToWatchlist(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getPrograms()->contains($program)) {
            $this->getUser()->removeProgram($program);
            $this->addFlash('danger','Le programme a été supprimé de la Wishlist');
        }
        else {
            $this->getUser()->addProgram($program);
            $this->addFlash('success','Le programme a bien été ajouté à la wishlist');
        }

        $entityManager->flush();

        return $this->json([
            'isInWatchlist' => $this->getUser()->IsinWatchlist($program)]);
    }
}
