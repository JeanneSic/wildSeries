<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/programs", name = "program_")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("/show/{id}", requirements={"id"="\d+"}, methods={"GET"}, name = "show")
     */
    public function show(int $id): Response
    {
        return $this->render('programs/show.html.twig', ['id' => $id]);
    }

}