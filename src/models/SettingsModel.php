<?php

namespace buzzingpixel\filecontent\models;

use craft\base\Model;

/**
 * Class SettingsModel
 */
class SettingsModel extends Model
{
    /**
     * Initializes model
     */
    public function init()
    {
        $this->basePath = CRAFT_BASE_PATH . '/fileContent';
    }

    /** @var string $basePath */
    public $basePath;
}
