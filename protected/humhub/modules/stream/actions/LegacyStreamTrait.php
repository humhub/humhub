<?php


namespace humhub\modules\stream\actions;


use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\models\Content;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Contains legacy stream action functionality.
 *
 * @package humhub\modules\stream\actions
 * @since 1.7
 */
trait LegacyStreamTrait
{
    /**
     * @var string
     * @deprecated since 1.6 use ActivityStreamAction
     */
    public $mode;

    /**
     * @var ActiveQuery
     * @deprecated since 1.7 use StreamQuery->query()
     */
    public $activeQuery;

    /**
     * First wall entry id to deliver
     *
     * @var int
     * @deprecated since 1.7 use $streamQuery->from
     */
    public $from;

    /**
     * Entry id of the top stream entry used for update requests
     *
     * @var int
     * @deprecated since 1.7 use $streamQuery->to
     */
    public $to;

    /**
     * Returns an array contains all information required to display a content
     * in stream.
     *
     * @param Content $content the content
     *
     * @return array
     * @throws Exception
     * @deprecated since 1.7
     */
    public static function getContentResultEntry(Content $content)
    {
        $result = [];

        // Get Underlying Object (e.g. Post, Poll, ...)
        $underlyingObject = $content->getPolymorphicRelation();
        if ($underlyingObject === null) {
            throw new Exception('Could not get contents underlying object! - contentid: ' . $content->id);
        }

        // Fix for newly created content
        if ($content->created_at instanceof Expression) {
            $content->created_at = date('Y-m-d G:i:s');
            $content->updated_at = $content->created_at;
        }

        $underlyingObject->populateRelation('content', $content);

        $result['output'] = static::renderEntry($underlyingObject, false);
        $result['pinned'] = (boolean) $content->pinned;
        $result['archived'] = (boolean) $content->archived;
        $result['guid'] = $content->guid;
        $result['id'] = $content->id;

        return $result;
    }

    /**
     * Renders the wallEntry of the given ContentActiveRecord.
     *
     * If setting $partial to false this function will use the renderAjax function instead of renderPartial, which
     * will directly append all dependencies to the result and if not used in a real ajax request will also append
     * the Layoutadditions.
     *
     * Render options can be provided by setting the $options array. This array will be passed to the WallEntryWidget implementation
     * of the given ContentActiveRecord. The render option array can for example be used to deactivate the rendering of the the WallEntryControls, Addons etc.
     *
     * The used jsWidget implementation of the WallEntry can be overwritten by $options['jsWidget'].
     *
     * e.g:
     *
     * ```php
     * Stream::renderEntry($myModel, [
     *      'jsWidget' => 'my.namespace.StreamEntry',
     *      'renderControls' => false
     * ]);
     * ```
     *
     * The previous example deactivated the rendering of the WallEntryControls and set a specific property of the WallEntryWidget related
     * to $myModel.
     *
     * @param ContentActiveRecord $record content record instance
     * @param $options array render options
     * @param boolean $partial whether or not to use renderPartial over renderAjax
     * @return string rendered wallentry
     * @throws \Exception
     * @deprecated since 1.7 use StreamEntryWidget::renderStreamEntry() instead
     */
    public static function renderEntry(ContentActiveRecord $record, $options =  [], $partial = true)
    {
        // TODO should be removed in next major version
        // Compatibility with pre 1.2.2
        if (is_bool($options)) {
            $partial = $options;
            $options = [];
        }

        if (!$record->wallEntryClass || !$record->content) {
            return '';
        }


        if(is_subclass_of($record->wallEntryClass, WallStreamEntryWidget::class, true)) {
            // This was added just in case we somehow run this with a new wall entry widget
            return StreamEntryWidget::renderStreamEntry($record);
        }

        return static::renderLegacyWallEntry( $record, $options, $partial);
    }

    /**
     * @param ContentActiveRecord $record
     * @param array $options
     * @param bool $partial
     * @return string
     * @throws \Exception
     * @since since 1.7
     */
    private static function renderLegacyWallEntry(ContentActiveRecord $record, $options = [], $partial = true)
    {
        if (isset($options['jsWidget'])) {
            $jsWidget = $options['jsWidget'];
            unset($options['jsWidget']);
        } else {
            $jsWidget = $record->getWallEntryWidget()->jsWidget;
        }


        if ($partial) {
            return Yii::$app->controller->renderPartial('@humhub/modules/content/views/layouts/wallEntry', [
                'content' => $record->getWallOut($options),
                'jsWidget' => $jsWidget,
                'entry' => $record->content
            ]);
        }

        return Yii::$app->controller->renderAjax('@humhub/modules/content/views/layouts/wallEntry', [
            'content' => $record->getWallOut($options),
            'jsWidget' => $jsWidget,
            'entry' => $record->content
        ]);
    }

    /**
     * Is inital stream requests (show first stream content)
     *
     * @return boolean Is initial request
     * @deprecated since 1.6 use StreamQuery::isInitialQuery
     */
    protected function isInitialRequest()
    {
        return $this->from === null && $this->to === null && $this->limit !== 1;
    }

    private function setDeprecatedActionProperties()
    {
        $this->activeQuery = $this->streamQuery->query();
        $this->from = $this->streamQuery->from;

        // Append additional filter of subclasses.
        $this->setupCriteria();
        $this->setupFilters();
    }
    /**
     * @deprecated since 1.7 use Stream::beforeApplyFilters()
     */
    public function setupCriteria(){}

    /**
     * @deprecated since 1.7 use Stream::beforeApplyFilters()
     */
    public function setupFilters(){ }

}
