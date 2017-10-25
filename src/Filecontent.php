<?php

namespace buzzingpixel\filecontent;

use buzzingpixel\filecontent\services\ReadFileContentsService;
use yii\base\Event;
use craft\base\Plugin;
use Mni\FrontYAML\Parser;
use craft\web\twig\variables\CraftVariable;
use Symfony\Component\Filesystem\Filesystem;
use buzzingpixel\filecontent\models\SettingsModel;
use buzzingpixel\filecontent\services\FileContentService;
use buzzingpixel\filecontent\variables\FilecontentVariable;

/**
 * Class Filecontent
 */
class Filecontent extends Plugin
{
    /** @var Filecontent $plugin */
    public static $plugin;

    /**
     * Initializes plugin
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('filecontent', FilecontentVariable::class);
            }
        );
    }

    /**
     * Create the settings model
     * @return SettingsModel
     */
    protected function createSettingsModel() : SettingsModel
    {
        return new SettingsModel();
    }

    /**
     * Gets the file content service with dependency injection
     * @return FileContentService
     */
    public function getFileContentService() : FileContentService
    {
        /** @var SettingsModel $settingsModel */
        $settingsModel = $this->getSettings();
        return new FileContentService([
            'basePath' => $settingsModel->basePath,
            'fileSystem' => new Filesystem(),
            'yamlMarkdownParser' => new Parser(),
        ]);
    }

    /**
     * Gets the read file contents service
     * @return ReadFileContentsService
     */
    public function getReadFileContentsService() : ReadFileContentsService
    {
        return new ReadFileContentsService();
    }
}
