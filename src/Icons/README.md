# Symfony UX Icons

## Installation

```bash
composer require symfony/ux-icons
```

## Add Icons

No icons are provided by this package. Add your svg icons to the `templates/icons/` directory and commit them.
The name of the file is used as the name of the icon (`name.svg` will be named `name`).

## Usage

```twig
{{ ux_icon('user-profile', {class: 'w-4 h-4'}) }} <!-- renders "user-profile.svg" -->

{{ ux_icon('sub-dir:user-profile', {class: 'w-4 h-4'}) }} <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->
```

### HTML Syntax

> [!NOTE]
> `symfony/ux-twig-component` is required to use the HTML syntax.

```html
<twig:UX:Icon name="user-profile" class="w-4 h-4" /> <!-- renders "user-profile.svg" -->

<twig:UX:Icon name="sub-dir:user-profile" class="w-4 h-4" /> <!-- renders "sub-dir/user-profile.svg" (sub-directory) -->
```

## Caching

To avoid having to parse icon files on every request, icons are cached.

During container warmup (`cache:warmup` and `cache:clear`), the icon cache is warmed.

> [!NOTE]
> During development, if you change an icon, you will need to clear the cache (`bin/console cache:clear`)
> to see the changes.

## Full Default Configuration

```yaml
ux_icons:
    # The local directory where icons are stored.
    icon_dir: '%kernel.project_dir%/templates/icons'

    # Default attributes to add to all icons.
    default_icon_attributes:
        # Default:
        fill: currentColor
```
