<?php

namespace App\Controller;

use App\Service\CommonMark\AdmonitionRenderer;
use App\Service\CommonMark\CodeExtension;
use App\Service\CommonMark\TabsRenderer;
use App\Service\CommonMark\VersionAdmonitionRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalink;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\RawMarkupContainerInterface;
use League\CommonMark\Node\StringContainerHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentationController extends AbstractController
{
    #[Route('/documentation', name: 'app_documentation')]
    public function pageAction(): Response
    {
        $markdown = $this->markdownConverter()->convert(file_get_contents(__DIR__.'/../../sample.md'));

        return $this->render('documentation.html.twig', [
            'markdown' => $markdown,
            'toc' => $this->generateTableOfContents($markdown->getDocument()),
        ]);
    }

    private function markdownConverter(): MarkdownConverter
    {
        $environment = (new Environment())
            ->addExtension(new CommonMarkCoreExtension())
            ->addExtension(new GithubFlavoredMarkdownExtension())
            ->addExtension(new HeadingPermalinkExtension())
            ->addExtension(new ExternalLinkExtension())
            ->addExtension(new CodeExtension())
            ->addExtension(new FootnoteExtension())
            ->addRenderer(BlockQuote::class, new AdmonitionRenderer(), 10)
            ->addRenderer(BlockQuote::class, new VersionAdmonitionRenderer(), 10)
            ->addRenderer(ListBlock::class, new TabsRenderer(), 10)
        ;

        return new MarkdownConverter($environment);
    }

    private function generateTableOfContents(Document $document): iterable
    {
        foreach ($document->iterator() as $node) {
            if (!$node instanceof HeadingPermalink) {
                continue;
            }

            $header = $node->parent();

            if (!$header instanceof Heading || 1 === $header->getLevel()) {
                continue;
            }

            yield [
                'level' => $header->getLevel() - 2,
                'text' => StringContainerHelper::getChildText($header, [RawMarkupContainerInterface::class]),
                'uri' => "#content-{$node->getSlug()}",
            ];
        }
    }
}
