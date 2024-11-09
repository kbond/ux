<?php

namespace App\Controller;

use App\Service\RecipeRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipes', name: 'app_recipes')]
    public function index(RecipeRegistry $recipes): Response
    {
        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recipes/{name}', name: 'app_recipe_show')]
    public function show(RecipeRegistry $recipes, string $name): Response
    {
        $recipe = $recipes->get($name);

        if (null === $recipe) {
            throw $this->createNotFoundException(sprintf('Recipe "%s" not found', $name));
        }

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
}
