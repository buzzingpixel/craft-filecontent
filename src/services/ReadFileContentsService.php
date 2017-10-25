<?php

namespace buzzingpixel\filecontent\services;

use craft\base\Component;

/**
 * Class ReadFileContentsService
 */
class ReadFileContentsService extends Component
{
    /**
     * Reads file contents
     * @param string $pathToFile
     * @param string $basePath
     * @return string
     */
    public function read(string $pathToFile, string $basePath = '') : string
    {
        // Set possible paths
        $paths = array(
            'publicPath' => rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/',
            'basePath' => CRAFT_BASE_PATH,
        );

        // Set the basePath
        $basePath = $paths[$basePath] ?? $paths['publicPath'];

        // Normalize path to file
        $pathToFile = ltrim($pathToFile, '/');

        // Set full file path
        $fullFilePath = "{$basePath}{$pathToFile}";

        // Check if the file exists
        if (! is_file($fullFilePath)) {
            return '';
        }

        return file_get_contents($fullFilePath) ?: '';
    }
}
