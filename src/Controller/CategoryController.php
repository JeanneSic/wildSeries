<?php


namespace App\Controller;

use App\Entity\Category;
use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
