<?php

namespace App\Tests\Functional;

use App\Model\Recipe;
use App\Service\RecipeRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RecipesTest extends KernelTestCase
{
    use HasBrowser;

    /**
     * @test
     */
    public function recipe_homepage(): void
    {
        $this->browser()
            ->visit('/recipes')
            ->assertSuccessful()
        ;
    }

    /**
     * @test
     */
    public function all_recipes_are_valid(): void
    {
        foreach (self::getContainer()->get(RecipeRegistry::class) as $recipe) {
            /* @var Recipe $recipe */
            $this->browser()
                ->throwExceptions()
                ->visit("/recipes/{$recipe->name}")
                ->assertSuccessful()
                ->assertSeeIn('h1', $recipe->title)
            ;
        }
    }
}
