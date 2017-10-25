<?php

namespace buzzingpixel\filecontent\variables;

use craft\helpers\Template;
use buzzingpixel\filecontent\Filecontent;

/**
 * Class FilecontentVariable
 */
class FilecontentVariable
{
    /**
     * Loads file content from requested path
     * @param string $path
     * @return mixed
     */
    public function load(string $path = '')
    {
        return Filecontent::$plugin
            ->getFileContentService()
            ->getContentFromPath($path);
    }

    /**
     * Loads listings from the requested path
     * @param string $path
     * @param array $options
     * @return array
     */
    public function loadListings(string $path = '', array $options = []) : array
    {
        return Filecontent::$plugin
            ->getFileContentService()
            ->getListingsFromPath($path, $options);
    }

    /**
     * Loads a listing item by slug
     * @param string $path
     * @param string $slug
     * @return array
     */
    public function loadListingBySlug(string $path, string $slug) : array
    {
        return Filecontent::$plugin
            ->getFileContentService()
            ->getListingItemBySlug($path, $slug);
    }

    /**
     * Reads a file's content
     * @param string $pathToFile
     * @param string $basePath
     * @return string
     */
    public function readFileContents(string $pathToFile, string $basePath = '') : string
    {
        return Template::raw(
            Filecontent::$plugin->getReadFileContentsService()->read(
                $pathToFile,
                $basePath
            )
        );
    }
}
