[![Release](https://img.shields.io/github/release/giterlizzi/dokuwiki-template-bootstrap3.svg)](https://github.com/giterlizzi/dokuwiki-template-bootstrap3/releases) [![License](https://img.shields.io/github/license/giterlizzi/dokuwiki-template-bootstrap3.svg)](https://github.com/giterlizzi/dokuwiki-template-bootstrap3) [![Starts](https://img.shields.io/github/stars/giterlizzi/dokuwiki-template-bootstrap3.svg)](https://github.com/giterlizzi/dokuwiki-template-bootstrap3) [![Forks](https://img.shields.io/github/forks/giterlizzi/dokuwiki-template-bootstrap3.svg)](https://github.com/giterlizzi/dokuwiki-template-bootstrap3) [![Issues](https://img.shields.io/github/issues/giterlizzi/dokuwiki-template-bootstrap3.svg)](https://github.com/giterlizzi/dokuwiki-template-bootstrap3/issues)

# Bootstrap3 template for DokuWiki

The bundled **Vanilla Pro** theme is selected by default. Choose it under
**Admin → Configuration Settings → Bootstrap3 → Bootstrap theme**.

## Features

  * HTML5 and CSS3
  * Responsive
  * Bootstrap 3.x based template
  * Glyphicons and FontAwesome icons
  * AnchorJS support
  * Typeahead support
  * High customizable via the configuration manager
  * Many html and DokuWiki hooks
  * Sidebar support ( left and/or right )
  * [Bootswatch](https://bootswatch.com) themes
  * Theme switcher
  * Cookie Law Banner Notice
  * Google Analytics integration
  * Avatar support for Gravatar, Libravatar and Office365

## Profile photos

The profile page includes an avatar upload form for members of `user` or `admin`
with media upload/overwrite permission in their own public namespace. JPEG, PNG
and WebP photos are auto-rotated, centre-cropped and resized to 120×120, then saved
as `user:<username>:public:avatar.png`. The local-initials avatar provider uses
this file. Uploads use DokuWiki's normal media permissions, revisions and logging;
PHP's upload limit still applies. **Reset photo** removes the local PNG and any
older avatar JPEG through DokuWiki's media deletion handler, restoring the
generated initials avatar on reload. Reset requires media deletion permission.
No core or authentication-plugin changes are needed.

## Text fonts

Choose **Admin → Configuration Settings → Bootstrap3 → Text font** to select a
font independently of the colour theme. The setting applies to page text,
headings and navigation. Code blocks, inline code and the editor use locally
bundled Source Code Pro independently of this setting.

Choices are Inter (the default), Source Sans 3 and Ubuntu.
Fonts are served locally, and the browser loads
only the font faces used by the page. Font size and line spacing are unchanged.
The new font families and their licences are bundled under `assets/fonts`.
Sources: [Source Sans 3](https://github.com/adobe-fonts/source-sans/tree/release/WOFF2/VF),
[Inter](https://github.com/rsms/inter/tree/master/docs/font-files) and
[Source Code Pro](https://github.com/adobe-fonts/source-code-pro/tree/release/WOFF2/VF).
Ubuntu reuses the font files already included with the template.

## Plugin Integration

  * Add New Page
  * Advanced
  * Bootstrap Wrapper
  * DataTables
  * Diagram
  * Discussion
  * Edittable
  * Explain
  * InlineTOC
  * Move
  * Publish
  * Rack
  * Tagging
  * Tags
  * Translation
  * Wrap - Tabs
  * jOrg Chart
  * ... and more!

## Localizations

  * Chinese
  * Czech
  * French
  * German
  * Hungarian
  * Indonesian
  * Italian
  * Japanese
  * Korean
  * Norwegian
  * Persian
  * Polish
  * Portuguese
  * Portuguese (Brazil)
  * Russian
  * Slovak
  * Spanish

# Installation

All documentation for this template can be found at
https://www.dokuwiki.org/template:bootstrap3

If you install this template manually, make sure it is installed in
`lib/tpl/bootstrap3/` - if the folder is called different it
will not work!

Ensure that the following PHP modules are installed: `php-xml`.

Please refer to https://www.dokuwiki.org/template for additional info
on how to install templates in DokuWiki.


----
Copyright (C) Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; version 2 of the License

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See the LICENSE file in your DokuWiki folder for details
