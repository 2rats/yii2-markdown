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
            $csrfParam = \Yii::$app->request->csrfParam;
            $csrfToken = \Yii::$app->request->csrfToken;
            MarkdownEditorUploadAsset::register($view);
            $view->registerJs("
                inlineAttachment.editors.codemirror4.attach($varName.codemirror, {
                    onFileUploadResponse: function(xhr) {
                        var result = JSON.parse(xhr.responseText);
                        filename = result[this.settings.jsonFieldName];
                        if (result && filename) {
                            var newValue;
                            newValue = this.settings.urlText.replace(this.filenameTag, filename);
                            var text = this.editor.getValue().replace(this.lastValue, newValue);
                            this.editor.setValue(text);
                            this.settings.onFileUploaded.call(this, filename);
                        }
                        return false;
                    },
                    extraParams: {
                        $csrfParam: '$csrfToken'
                    },
                    uploadUrl: '" . $this->uploadUrl . "',
                    jsonFieldName: 'filename',
                    urlText: \"![" . \Yii::t('app', 'Image description') . "]({filename})\"
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

        $this->editorOptions['toolbar'] = $this->getToolbarConfig();

        return Json::encode($this->editorOptions);
    }

    protected function getToolbarConfig() {
        $toolbar =  [
            'bold', 'italic', 'heading-1', 'heading-2', 'heading-3', '|',
            'code', 'unordered-list', 'ordered-list', '|',
            'link', 'image', 
        ];
        
        if($this->uploadUrl !== null) {
            $toolbar[] = [
                'name' => 'image-upload',
                'action' => new JsExpression('function customFunction(editor){
                    let inputId = "editor-image-upload-input";
                    if(document.getElementById(inputId) === null){
                        let input = document.createElement("input");
                        input.type = "file";
                        input.accept = "image/*";
                        input.style.display = "none";
                        input.id = "editor-image-upload-input";
                        input.onchange = function(){
                            let file = this.files[0];
                            let formData = new FormData();
                            formData.append("file", file);
                            formData.append("' . \Yii::$app->request->csrfParam . '", "' . \Yii::$app->request->getCsrfToken() . '");
                            fetch("' . $this->uploadUrl . '", {
                                method: "POST",
                                body: formData
                            }).then(response => response.json()).then(data => {
                                editor.codemirror.replaceSelection("![' . \Yii::t('app', 'Image description') . '](" + data.filename + ")");
                            });
                        }
                        document.body.appendChild(input);
                    }
                    document.getElementById(inputId).click();

                }'),
                'className' => 'fa fa-file-image',
                'title' => 'Upload image',
            ];
        }
        $toolbar = array_merge($toolbar, [
            'table', 'horizontal-rule', '|',
            'preview', 'fullscreen'
        ]);
        return $toolbar;
    }
}
