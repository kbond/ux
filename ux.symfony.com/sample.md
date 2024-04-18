# Sample Documentation Page

Lorem _ipsum dolor sit amet_, consectetur[^note1] adipiscing elit. **Donec dignissim velit eget** interdum pulvinar. Curabitur vitae nunc et ligula auctor euismod ac ut odio. Phasellus eget euismod ante. Etiam maximus, orci ac convallis dictum, dolor neque finibus massa, in luctus risus felis non nisi. Curabitur non elit facilisis, rhoncus dolor ultrices, condimentum lorem. Vivamus a lacus ac ipsum volutpat venenatis. Duis eu lectus at est vestibulum auctor non id velit. Morbi in dolor iaculis, accumsan dui vitae, molestie purus. Pellentesque gravida aliquam turpis, quis dictum ligula ultricies ut. Vivamus dignissim odio in ultricies varius. Quisque tincidunt massa turpis, fringilla condimentum tellus lacinia sed.

- ===Tab item 1
    ```
    List item 1 content
    ```

- ===Tab item 2
    ```
    Tab item 2 content
    ```

- ===Tab item 3
    ```
    Tab item 3 content
    ```

    more stuff

> [!NOTE]
> This is a note

> [!TIP]
> This is a tip

> [!WARNING]
> This is a warning
> 
> Mulitple lines

> [!IMPORTANT]
> This is important

> [!CAUTION]
> This is a caution

> [!DANGER]
> This is a danger

## Header 1

> [!VERSION 2.2]
> This is a version added note

Vestibulum sed libero auctor, `gravida dolor eu, ornare mi`. [Outbound link](https://symfony.com) non gravida mi. Sed [inline link](/changelog) ac
est placerat, et fringilla nibh auctor. Fusce bibendum pellentesque lorem ac accumsan. Morbi quis gravida lectus,
eget congue libero. [Reference Link][1] nulla dolor, a luctus tortor commodo et.

> A Blockquote with stuff
> ```twig
> {% block content %}
> ```

Duis rutrum purus et nulla rhoncus
hendrerit. Phasellus bibendum, eros dignissim accumsan ullamcorper, augue magna porta felis, vitae ornare metus nibh
ac nisl. Etiam eu urna magna. Donec sed mi ut augue euismod elementum. Fusce at odio nunc.

### Header 1.1

- list 1
  - list 1.1
    - list 1.1.1
  - list 1.2
- list 2
- list 3

#### Header 1.1.1

1. list 1
2. list 2
3. list 3

##### Header 1.1.1.1

```php
<?php

namespace App\Controller;

use League\CommonMark\MarkdownConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentationController extends AbstractController
{
    #[Route('/documentation', name: 'app_documentation')]
    public function pageAction(MarkdownConverter $markdownConverter): Response
    {
        $markdown = $markdownConverter->convert(file_get_contents(__DIR__.'/../../sample.md'));

        return $this->render('documentation.html.twig', [
            'markdown' => $markdown,
        ]);
    }
}
```

###### Header 1.1.1.1.1

| th | th | th |
|----|----|----|
| td | td | td |

## Header 2

[^note1]: Elit Malesuada Ridiculus

[1]: /changelog
