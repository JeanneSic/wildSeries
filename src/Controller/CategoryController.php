<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Program;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/categories", name = "category_")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * The controller for the category add form
     *
     * @Route("/new", name = "new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request) : Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Getting a category by its name
     * @Route("/{categoryName}", methods={"GET"}, name="show")
     * @param string $categoryName
     * @return Response A response instance
     */
    public function show(string $categoryName) {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            -> findOneBy(['name' => $categoryName]);

        if (!$category) {
            throw new NotFoundHttpException("Aucune catégorie ne correspond à " . $categoryName);
        }

        $programsByCategory = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['category' => $category->getId()],
                ['id' => 'DESC'],
                3
            );

        return $this->render('category/show.html.twig', ['programsByCategory' => $programsByCategory]);
    }



}
