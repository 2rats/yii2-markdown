<?php

namespace rats\markdown;

use yii\web\AssetBundle;

/**
 * Class MarkdownEditorUploadAsset
 *
 * @package rats\markdown
 */
class MarkdownEditorUploadAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@rats/markdown/assets';

    /**
     * @var array
     */
    public $js = [
        'inline-attachment.js',
        'codemirror-4.inline-attachment.js',
        'input.inline-attachment.js',
    ];

    /**
     * @var array
     */
    public $depends = [
        'rats\markdown\MarkdownEditorAsset',
        'yii\web\YiiAsset',
    ];
}
