# Search API language fallback

A Drupal module providing language fallback integration for Search API.

## What it does

- Improves "Complete entity view" alter callback to support language fallback.
- Removes node links (on not panelized view modes).

## Fulltext search recipe

- Required modules:
    - [search_api_et 7.x-2.x-all-site-languages](https://github.com/AmazeeLabs/search_api_et/tree/7.x-2.x-all-site-languages)
    - [search_api_page](https://www.drupal.org/project/search_api_page)
    - [language_fallback 7.x-2.x](https://www.drupal.org/project/language_fallback) (or [7.x-2.x-amazee](https://github.com/AmazeeLabs/language_fallback/tree/7.x-2.x-amazee), see [blog post](http://www.amazeelabs.com/en/blog/total-language-fallback))
    - search_api_et_solr is **not** required
- Prepare node view mode. The existing one can be used, or new one created. It can be panelized or not. Recommendations: only include fields that are required for search; hide field labels.
- Setup Search API index:
    - Item type: Multilingual Content
    - Languages to be included in the index: all site languages
    - Fields: Node ID, Author, Entity HTML output
    - Filters: Complete entity view (set the prepared view mode)

## Known issues

- If the title field is included to the view mode, it can be shown in the search excerpt. If it isn't included, it is not available for search.
