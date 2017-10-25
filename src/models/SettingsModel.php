<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2017 BuzzingPixel, LLC
 * @license Apache-2.0
 */

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
