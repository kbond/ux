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
final class AdmonitionRenderer implements NodeRendererInterface
{
    private const TYPES = [
        'NOTE',
        'TIP',
        'IMPORTANT',
        'WARNING',
        'CAUTION',
        'DANGER',
    ];

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): null|string|\Stringable
    {
        if (!$parsed = self::parseBlockQuote($node)) {
            return null;
        }

        [$textNode, $type] = $parsed;

        $textNode->detach();

        return new HtmlElement(
            'div',
            ['class' => 'Admonition Admonition--' . $type],
            $childRenderer->renderNodes($node->children())
        );
    }

    /**
     * @return array{Text,string}|null
     */
    private static function parseBlockQuote(Node $node): ?array
    {
        $textNode = $node->firstChild()?->firstChild();

        if (!$textNode instanceof Text || !\preg_match('#^\[!([A-Z]+)]$#', $textNode->getLiteral(), $matches)) {
            return null;
        }

        $type = $matches[1];

        if (!\in_array($type, self::TYPES, true)) {
            return null;
        }

        return [$textNode, \mb_strtolower($type)];
    }
}
