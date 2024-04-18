<?php

namespace App\Service\CommonMark;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class VersionAdmonitionRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): null|string|\Stringable
    {
        if (!$parsed = self::parseBlockQuote($node)) {
            return null;
        }

        [$textNode, $version] = $parsed;

        $textNode->detach();

        return new HtmlElement(
            'div',
            ['class' => 'Admonition Admonition--version'],
            implode("\n", [
                new HtmlElement('span', ['class' => 'Admonition--title'], $version),
                $childRenderer->renderNodes($node->children()),
            ])
        );
    }

    /**
     * @return array{Text,string}|null
     */
    private static function parseBlockQuote(Node $node): ?array
    {
        $textNode = $node->firstChild()?->firstChild();

        if (!$textNode instanceof Text || !\preg_match('#^\[!VERSION (\d+\.\d+)]$#', $textNode->getLiteral(), $matches)) {
            return null;
        }

        return [$textNode, $matches[1]];
    }
}
