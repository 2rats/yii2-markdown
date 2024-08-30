<?php

namespace rats\markdown;

use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * Class MarkdownEditor
 *
 * @package rats\markdown
 */
class MarkdownEditor extends InputWidget
{
    /**
     * @var ?string upload url, if null upload feature will be disabled
     */
    public $uploadUrl = null;

    /**
     * @var array markdown options
     */
    public $editorOptions = [];

    /**
     * Renders the widget.
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->attribute, $this->value, $this->options);
        }

        $this->registerAssets();
    }

    /**
     * Register client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        MarkdownEditorAsset::register($view);
        $varName = Inflector::variablize('editor_' . $this->id);
        $script = "var {$varName} = new SimpleMDE(" . $this->getEditorOptions() . ');';
        $view->registerJs($script);

        if ($this->uploadUrl !== null) {
            MarkdownEditorUploadAsset::register($view);
            $view->registerJs("
                inlineAttachment.editors.codemirror4.attach(simplemde.codemirror, {
                    onFileUploadResponse: function(xhr) {
                        var result = JSON.parse(xhr.responseText);
                        console.log(result);
                        filename = result[this.settings.jsonFieldName];
                        console.log(filename);
                        console.log(this.filenameTag);
                        if (result && filename) {
                            var newValue;
                            if (typeof this.settings.urlText === 'function') {
                                newValue = this.settings.urlText.call(this, filename, result);
                            } else {
                                newValue = this.settings.urlText.replace(this.filenameTag, filename);
                            }
                            console.log(newValue);
                            var text = this.editor.getValue().replace(this.lastValue, newValue);
                            this.editor.setValue(text);
                            this.settings.onFileUploaded.call(this, filename);
                        }
                        return false;
                    },
                    uploadUrl: '" . $this->uploadUrl . "',
                    jsonFieldName: 'filename',
                    urlText: \"![Image]({filename})\"
                });
            ");
        }
    }

    /**
     * Return editor options in json format
     *
     * @return string
     */
    protected function getEditorOptions()
    {
        $this->editorOptions['element'] = new JsExpression('document.getElementById("' . $this->options['id'] . '")');

        return Json::encode($this->editorOptions);
    }
}
