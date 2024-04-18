<?php

namespace App\Service\CommonMark;

use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TabsRenderer implements NodeRendererInterface
{
    private const THEME = [
        'header' => [
            'attributes' => ['class' => 'nav nav-tabs', 'role' => 'tablist'],
            'tab' => [
                'attributes' => ['class' => 'nav-item', 'role' => 'presentation'],
                'trigger' => [
                    'tag' => 'button',
                    'attributes' => ['id' => '{tabId}', 'class' => 'nav-link', 'type' => 'button', 'role' => 'tab', 'aria-selected' => 'false', 'data-bs-toggle' => 'tab', 'data-bs-target' => '#{panelId}', 'aria-controls' => '{panelId}'],
                    'active_attributes' => ['class' => 'nav-link active', 'aria-selected' => 'true'],
                ],
            ],
        ],
        'body' => [
            'attributes' => ['class' => 'tab-content'],
            'panel' => [
                'attributes' => ['id' => '{panelId}', 'class' => 'tab-pane fade', 'role' => 'tabpanel', 'tabindex' => '0', 'aria-labelledby' => '{tabId}'],
                'active_attributes' => ['class' => 'tab-pane fade show active'],
            ],
        ],
    ];

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string|\Stringable|null
    {
        $firstItemNode = $node->firstChild()?->firstChild()?->firstChild();

        if (!$firstItemNode instanceof Text || !\str_starts_with($firstItemNode->getLiteral(), '===')) {
            return null;
        }

        $tabs = [];
        $panels = [];
        $id = 'tabs-'.\bin2hex(\random_bytes(5));

        foreach ($node->children() as $i => $item) {
            [$title, $children] = self::parseItem($item);
            $tabId = $id.'-tab-'.$i;
            $panelId = $id.'-panel-'.$i;

            $tabAttributes = self::THEME['header']['tab']['attributes'] ?? [];
            $triggerAttributes = self::THEME['header']['tab']['trigger']['attributes'] ?? [];
            $panelAttributes = self::THEME['body']['panel']['attributes'] ?? [];

            if (0 === $i) {
                $tabAttributes = \array_merge($tabAttributes, self::THEME['header']['tab']['active_attributes'] ?? []);
                $triggerAttributes = \array_merge($triggerAttributes, self::THEME['header']['tab']['trigger']['active_attributes'] ?? []);
                $panelAttributes = \array_merge($panelAttributes, self::THEME['body']['panel']['active_attributes'] ?? []);
            }

            $tabAttributes = self::renderIds($tabAttributes, $tabId, $panelId);
            $triggerAttributes = self::renderIds($triggerAttributes, $tabId, $panelId);
            $panelAttributes = self::renderIds($panelAttributes, $tabId, $panelId);

            $tabs[] = new HtmlElement(
                'li',
                $tabAttributes,
                contents: new HtmlElement(
                    'button',
                    $triggerAttributes,
                    contents: $title,
                ),
            );
            $panels[] = new HtmlElement(
                'div',
                $panelAttributes,
                contents: $childRenderer->renderNodes($children),
            );
        }

        $header = new HtmlElement(
            'ul',
            self::THEME['header']['attributes'] ?? [],
            contents: \implode("\n", $tabs),
        );
        $content = new HtmlElement(
            'div',
            self::THEME['body']['attributes'] ?? [],
            contents: \implode("\n", $panels),
        );

        return new HtmlElement('div', self::THEME['attributes'] ?? [], contents: $header."\n".$content);
    }

    /**
     * @param array<string,string> $attributes
     *
     * @return array<string,string>
     */
    private static function renderIds(array $attributes, string $tabId, string $panelId): array
    {
        return \array_map(
            static fn(string $value): string => \str_replace(['{tabId}', '{panelId}'], [$tabId, $panelId], $value),
            $attributes
        );
    }

    /**
     * @return array{string,?string,iterable<Node>}
     */
    private static function parseItem(Node $node): array
    {
        $firstChild = $node->firstChild();

        if (!$firstChild instanceof Paragraph) {
            throw new \RuntimeException('Expected first child to be a paragraph.');
        }

        $firstChild->detach();
        $textNode = $firstChild->firstChild();

        if (!$textNode instanceof Text) {
            throw new \RuntimeException('Expected first child of paragraph to be a text node.');
        }

        $text = $textNode->getLiteral();

        if (!\str_starts_with($text, '===')) {
            throw new \RuntimeException('Expected text to start with "===".');
        }

        return [\mb_substr($text, 3), $node->children()];
    }
}
