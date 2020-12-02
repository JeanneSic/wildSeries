<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\ProgramType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/programs", name = "program_")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render('programs/index.html.twig', [
            'programs' => $programs,
        ]);
    }

    /**
     * @Route("/new", name = "new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($program);
            $entityManager->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('programs/new.html.twig', ["form" => $form->createView()]);
    }


    /**
     * Getting a program by id
     *
     * @Route("/show/{id}", requirements={"id"="\d+"}, methods={"GET"}, name = "show")
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
     * @Route("/{programId}/seasons/{seasonId}", methods={"GET"}, name = "season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
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
     * @Route("/{programId}/seasons/{seasonId}/episodes/{episodeId}", methods={"GET"}, name = "episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programId": "id"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeId": "id"}})
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     * @return Response
     */
    public function showEpisode(Program $program, Season $season, Episode $episode)
    {
        return $this->render('programs/episode_show.html.twig', ['program'=> $program, 'season'=> $season, 'episode' => $episode]);
    }

}