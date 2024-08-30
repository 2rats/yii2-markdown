<?php

namespace rats\markdown;

use yii\web\AssetBundle;

/**
 * Class MarkdownEditorAsset
 *
 * @package rats\markdown
 */
class MarkdownEditorAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@rats/markdown/assets';

    /**
     * @var array
     */
    public $css = [
        'simplemde.min.css',
    ];

    /**
     * @var array
     */
    public $js = [
        'simplemde.min.js',
        'inline-attachment.js',
        'codemirror-4.inline-attachment.js',
        'input.inline-attachment.js',
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
