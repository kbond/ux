<?php

namespace App\Controller;

use App\Service\RecipeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'app_recipes', methods: 'GET',)]
    public function index(RecipeRegistry $registry): Response
    {
        return $this->render('recipe/index.html.twig', [
            'recipes' => $registry->all(),
        ]);
    }

    #[Route('/recipes/{name}', name: 'app_recipe_show', methods: 'GET',)]
    public function show(string $name, RecipeRegistry $registry): Response
    {
        return $this->render('recipe/show.html.twig', [
            'recipe' => $registry->get($name) ?? throw $this->createNotFoundException(sprintf('Recipe "%s" not found', $name)),
        ]);
    }
}
