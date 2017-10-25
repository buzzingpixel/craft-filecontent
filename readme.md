# Craft File Content
## Add flat file content capability to your CraftCMS site

A Craft 3 plugin that adds Twig variables for getting flat file content.

## Configuration

There is one globally configurable option: `basePath`. This tells the plugin where to look for file content. By default, it looks in `CRAFT_BASE_PATH . '/fileContent'`.

## Usage

### `load`

Example:

```twig
{% set pageContent = craft.filecontent.load('my/custom/path') %}
```

If `my/custom/path` is a directory, the directory will be searched recursively for `.md` files, `.json` files, and `.php` files. Each of those files parsed contents will be added to an array with the filename as the key.

If `my/custom/path.md` exists, the contents of that file will be parsed and returned.

If `my/custom/path.json` exists, the contents of that file will be parsed and returned.

If `my/custom/path.php` exists, the contents of that file will be parsed and returned.

Markdown files are parsed through a Yaml front matter parser. The content portion of the markdown file is placed in a array key called 'content'.

JSON files are parsed as expected.

PHP files must return an array.

### `loadListings`

Example:

```twig
{% set blogIndex = craft.filecontent.loadListings('my/custom/path', {
    offset: 10,
    limit: 10,
    sort: 'asc'
}) %}
```

All files in the specified directory are loaded and returned. The file name format is as follows: `2015-10-26--04-00-pm--my-post-slug.md`.

`.json` and `.php` are also accepted.

If `index.md`, `index.json`, and `index.php` is found in the directory, it's contents will be added on the key `meta` and does not factor in to count, limit, and offset.

### `loadListingBySlug`

Example:

```twig
{% set blogPost = craft.filecontent.loadListingBySlug('my/custom/path', 'my-slug') %}
```

Get's a file post content by slug.

### `readFileContents`

Example:

```twig
{% set criticalCss = craft.filecontent.readFileContents(cricitalCssPath)|raw %}
```

Reads any file's raw text content. As this is not related to the flat file content functionality, it does not look in the configured basepath, but instead defaults to `$_SERVER['DOCUMENT_ROOT']`. You can optionally specify a second parameter as the base path.
