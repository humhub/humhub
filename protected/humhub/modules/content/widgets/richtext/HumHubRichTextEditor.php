<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;

/**
 * Legacy rich text editor implementation.
 *
 * @deprecated since 1.3 this is the legacy rich text editor implementation which won't be maintained in the future.
 */
class HumHubRichTextEditor extends AbstractRichTextEditor
{
    /**
     * Defines the javascript picker implementation.
     *
     * @var string
     */
    public $jsWidget = 'ui.richtext.Richtext';

    /**
     * @inheritdoc
     */
    public static $renderer = [
        'class' => HumHubRichText::class
    ];

    public function getAttributes()
    {
        return [
            'class' => "atwho-input form-control humhub-ui-richtext",
            'contenteditable' => "true",
        ];
    }


}
