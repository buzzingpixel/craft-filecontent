<?php

namespace buzzingpixel\filecontent\services;

use DateTime;
use craft\base\Component;
use Mni\FrontYAML\Parser;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FileContentService
 */
class FileContentService extends Component
{
    const EXTENSION_MAP = [
        'md' => 'markdown',
        'markdown' => 'markdown',
        'txt' => 'markdown',
        'json' => 'json',
        'js' => 'json',
        'php' => 'php',
    ];

    /** @var string $basePath */
    private $basePath;

    /** @var Filesystem $fileSystem */
    private $fileSystem;

    /** @var Parser */
    private $yamlMarkdownParser;

    /**
     * FileContentService constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Run the parent constructor
        parent::__construct();

        // Do our own stuff
        foreach ($config as $key => $val) {
            $this->{$key} = $val;
        }
    }

    /**
     * Get listings from path
     * @param string $path
     * @param array $options
     * @return array
     */
    public function getListingsFromPath(
        string $path = '',
        array $options = []
    ) : array {
        $options = array_merge([
            'limit' => 100,
            'offset' => 0,
            'sort' => 'desc',
        ], $options);

        $sep = DIRECTORY_SEPARATOR;
        $basePath = rtrim($this->basePath, $sep);
        $path = ltrim($path, $sep);
        $fullPath = "{$basePath}{$sep}{$path}";

        if (! is_dir($fullPath)) {
            return array();
        }

        $content = $this->getContentFromDirPath($fullPath);

        $meta = $content['index'] ?? [];
        unset($content['index']);

        if ($options['sort'] === 'desc') {
            $content = array_reverse($content, true);
        }

        foreach ($content as $itemMeta => &$item) {
            $slug = $itemMeta;
            $itemMeta = explode('--', $itemMeta);
            $ymd = '1970-01-01';
            $time = '00-00-pm';

            if (count($itemMeta) === 1) {
                $slug = $itemMeta[0];
            } elseif (count($itemMeta) === 2) {
                list($ymd, $slug) = $itemMeta;
            } elseif (count($itemMeta) === 3) {
                list($ymd, $time, $slug) = $itemMeta;
            }

            $ymd = explode('-', $ymd);
            $time = explode('-', $time);

            $year = str_pad((int) ($ymd[0] ?? 1970), 4, 0, STR_PAD_LEFT);
            $month = str_pad((int) ($ymd[1] ?? 1), 2, 0, STR_PAD_LEFT);
            $day = str_pad((int) ($ymd[2] ?? 1), 2, 0, STR_PAD_LEFT);
            $hour = str_pad((int) ($time[0] ?? 0), 2, 0, STR_PAD_LEFT);
            $minute = str_pad((int) ($time[1] ?? 0), 2, 0, STR_PAD_LEFT);
            $amPm = $time ?? 'am';
            $amPm = $amPm === 'am' || $amPm === 'pm' ? $amPm : 'am';

            $dateTime = new DateTime(
                "{$year}-{$month}-{$day} {$hour}:{$minute} {$amPm}"
            );

            $item['slug'] = $slug;
            $item['year'] = $year;
            $item['month'] = $month;
            $item['day'] = $day;
            $item['hour'] = $hour;
            $item['minute'] = $minute;
            $item['amPm'] = $amPm;
            $item['dateTime'] = $dateTime;
        }

        unset($item);

        $absoluteTotal = count($content);

        $content = array_splice(
            $content,
            $options['offset'],
            $options['limit']
        );

        return [
            'meta' => $meta,
            'items' => array_values($content),
            'absoluteTotal' => $absoluteTotal,
        ];
    }

    /**
     * Gets a listing item by slug
     * @param string $path
     * @param string $slug
     * @return array
     */
    public function getListingItemBySlug(string $path, string $slug) : array
    {
        $listingItems = $this->getListingsFromPath($path);

        if (! isset($listingItems['meta'], $listingItems['items'])) {
            return array();
        }

        foreach ($listingItems['items'] as $item) {
            if ($item['slug'] !== $slug) {
                continue;
            }

            return [
                'meta' => $listingItems['meta'],
                'item' => $item,
            ];
        }

        return [];
    }

    /**
     * Gets file content from path
     * @param string $path
     * @return mixed
     */
    public function getContentFromPath(string $path = '')
    {
        $sep = DIRECTORY_SEPARATOR;
        $basePath = rtrim($this->basePath, $sep);
        $path = ltrim($path, $sep);
        $fullPath = "{$basePath}{$sep}{$path}";

        if (is_dir($fullPath)) {
            return $this->getContentFromDirPath($fullPath);
        }

        return $this->getContentFromFilePath($fullPath);
    }

    /**
     * Gets content from directory path
     * @param string $fullPath
     * @return mixed
     */
    private function getContentFromDirPath(string $fullPath)
    {
        $vars = [];

        foreach (new \DirectoryIterator($fullPath) as $file) {
            // Make sure this is actually a file
            if ($file->isDot()) {
                continue;
            }

            $thisVars = $this->getContentFromFilePath($file->getPathname());

            if (! is_array($thisVars)) {
                continue;
            }

            $name = $file->getBasename(".{$file->getExtension()}");

            $vars[$name] = $this->getContentFromFilePath($file->getPathname());
        }

        return $vars;
    }

    /**
     * Gets content from file path
     * @param string $fullPath
     * @return mixed
     */
    private function getContentFromFilePath(string $fullPath)
    {
        $pathinfo = pathinfo($fullPath);

        $parser = false;

        $hasExt = isset($pathinfo['extension']);

        if ($hasExt) {
            if (! isset(self::EXTENSION_MAP[$pathinfo['extension']]) ||
                ! $this->fileSystem->exists($fullPath)
            ) {
                return null;
            }

            $parser = self::EXTENSION_MAP[$pathinfo['extension']];
        }

        if (! $hasExt) {
            foreach (self::EXTENSION_MAP as $ext => $parserStr) {
                $thisPath = "{$fullPath}.{$ext}";

                if (! $this->fileSystem->exists($thisPath)) {
                    continue;
                }

                $fullPath = $thisPath;

                $parser = $parserStr;

                break;
            }
        }

        if (! $parser) {
            return null;
        }

        if ($parser === 'markdown') {
            return $this->parseMarkdown($fullPath);
        }

        if ($parser === 'json') {
            return $this->parseJson($fullPath);
        }

        return $this->parsePHP($fullPath);
    }

    /**
     * Parses markdown file
     * @param string $fullPath
     * @return mixed
     */
    private function parseMarkdown(string $fullPath)
    {
        $document = $this->yamlMarkdownParser->parse(
            file_get_contents($fullPath)
        );

        $vars = $document->getYAML();

        $vars['content'] = $document->getContent();

        return $vars;
    }

    /**
     * Parses json file
     * @param string $fullPath
     * @return mixed
     */
    private function parseJson(string $fullPath)
    {
        return json_decode(file_get_contents($fullPath), true);
    }

    /**
     * Parses markdown file
     * @param string $fullPath
     * @return mixed
     */
    private function parsePHP(string $fullPath)
    {
        return include $fullPath;
    }
}
