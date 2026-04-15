<?php

namespace humhub\modules\space\widgets;

use Exception;
use humhub\components\Widget;
use humhub\helpers\Html;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\permissions\SpaceDirectoryAccess;
use humhub\modules\space\widgets\Image;
use humhub\modules\space\widgets\vue\SpaceChooserWidget;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\Follow;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;

/**
 * Class Chooser
 * @package humhub\modules\space\widgets
 */
class Chooser extends Widget
{
    /**
     * @var bool
     * @since 1.10
     */
    public $lazyLoad = true;

    /**
     * @var string
     */
    public $viewName = '@space/widgets/views/spaceChooser';

    /**
     * @return bool
     */
    public function beforeRun()
    {
        if (!$this->canRun()) {
            return false;
        }

        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function run()
    {
        return SpaceChooserWidget::widget([
            'props' => $this->getVueProps(),
        ]);
    }

    /**
     * @return bool
     */
    protected function canRun()
    {
        return !Yii::$app->user->isGuest;
    }

    /**
     * @return string
     */
    protected function getNoSpaceHtml()
    {
        $html = Icon::get('dot-circle-o') . '<br>' . Yii::t('SpaceModule.chooser', 'My spaces');
        return Html::tag('div', $html, ['class' => 'no-space']);
    }

    /**
     * @return array
     * @throws Throwable
     * @throws InvalidConfigException
     */
    protected function getVueProps()
    {
        $currentSpace = $this->getCurrentSpace();

        return [
            'lazyLoad' => $this->lazyLoad,
            'lazySearchUrl' => Url::to(['/space/browse/search-lazy']),
            'remoteSearchUrl' => Url::to(['/space/browse/search-json']),
            'directoryUrl' => Url::to(['/space/spaces']),
            'createSpaceUrl' => Url::to(['/space/create/create']),
            'canCreateSpace' => $this->canCreateSpace(),
            'canAccessDirectory' => Yii::$app->user->can(SpaceDirectoryAccess::class),
            'currentSpaceImage' => $currentSpace
                ? Image::widget(['space' => $currentSpace, 'width' => 32, 'htmlOptions' => ['class' => 'current-space-image']])
                : '',
            'directoryIcon' => Icon::get('directory')->asString(),
            'noSpaceHtml' => $this->getNoSpaceHtml(),
            'spaces' => $this->lazyLoad ? [] : $this->getSpaceResults(),
            'text' => [
                'search' => Yii::t('SpaceModule.chooser', 'Search'),
                'searchForSpaces' => Yii::t('SpaceModule.chooser', 'Search for spaces'),
                'createSpace' => Yii::t('SpaceModule.chooser', 'Create Space'),
                'remoteAtLeastInput' => Yii::t('SpaceModule.chooser', 'Please enter at least {count} characters to search Spaces.', ['count' => 2]),
                'emptyOwnResult' => Yii::t('SpaceModule.chooser', 'You are not a member of or following any Spaces.'),
                'emptyResult' => Yii::t('SpaceModule.chooser', 'No Spaces found.'),
            ],
        ];
    }

    /**
     * @param string $output
     * @return mixed|string
     * @throws Throwable
     */
    protected function renderItems($output = '')
    {
        if ($this->lazyLoad) {
            return $output;
        }

        // render membership items
        foreach ($this->getMemberships() as $membership) {
            $result = SpaceChooserItem::widget([
                'space' => $membership->space, 'updateCount' => $membership->countNewItems(), 'isMember' => true,
            ]);

            $output = $this->attachItem($output, $result);
        }

        // render follow spaces
        foreach ($this->getFollowSpaces() as $space) {
            $result = SpaceChooserItem::widget(['space' => $space, 'isFollowing' => true]);
            $output = $this->attachItem($output, $result);
        }

        return $output;
    }

    /**
     * @return array
     * @throws Throwable
     */
    protected function getSpaceResults()
    {
        $results = [];
        $guids = [];

        foreach ($this->getMemberships() as $membership) {
            if (isset($guids[$membership->space->guid])) {
                continue;
            }

            $guids[$membership->space->guid] = true;
            $results[] = self::getSpaceResult($membership->space, true, [
                'updateCount' => $membership->countNewItems(),
                'isMember' => true,
            ]);
        }

        foreach ($this->getFollowSpaces() as $space) {
            if (isset($guids[$space->guid])) {
                continue;
            }

            $guids[$space->guid] = true;
            $results[] = self::getSpaceResult($space, true, ['isFollowing' => true]);
        }

        return $results;
    }

    /**
     * If array passed to ouput, it will be added as ['output' => 'string']
     * This is used for passing rendered items as json array to lazy load
     * See getLazyLoadResult of the same class
     *
     * @param $output
     * @param $result
     * @return mixed|string
     */
    protected function attachItem($output, $result)
    {
        if (is_array($output)) {
            $output[] = ['output' => $result];
        } elseif (is_string($output)) {
            $output .= $result;
        }

        return $output;
    }

    /**
     * @return Space[]
     * @throws Throwable
     */
    protected function getFollowSpaces()
    {
        if (!Yii::$app->user->isGuest) {
            return Follow::getFollowedSpacesQuery(Yii::$app->user->getIdentity())->all();
        }

        return [];
    }

    /**
     * @return Membership[]
     * @throws Throwable
     */
    protected function getMemberships()
    {
        if (!Yii::$app->user->isGuest) {
            return Membership::findByUser(Yii::$app->user->getIdentity())->all();
        }

        return [];
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    protected function canCreateSpace()
    {
        /** @var PermissionManager $manager */
        $manager = Yii::$app->user->permissionmanager;
        return $manager->can(new CreatePublicSpace()) || $manager->can(new CreatePrivateSpace());
    }

    /**
     * @return Space | null
     */
    protected function getCurrentSpace()
    {
        if (!Yii::$app->controller instanceof ContentContainerController) {
            return null;
        }

        if ((Yii::$app->controller->contentContainer ?? null) instanceof Space) {
            return Yii::$app->controller->contentContainer;
        }

        return null;
    }

    /**
     * @param Space $space
     * @param bool $withChooserItem
     * @param array $itemOptions
     * @return array
     * @throws Exception
     */
    public static function getSpaceResult($space, $withChooserItem = true, $itemOptions = [])
    {
        $spaceInfo = [
            'guid' => $space->guid, 'title' => $space->name, 'tags' => Html::encode(implode(', ', $space->getTags())),
            'image' => Image::widget(['space' => $space, 'width' => 24]), 'link' => $space->getUrl(),
        ];

        if ($withChooserItem) {
            $options = array_merge(['space' => $space, 'isMember' => false, 'isFollowing' => false], $itemOptions);
            $spaceInfo['output'] = SpaceChooserItem::widget($options);
        }

        return $spaceInfo;
    }

    /**
     * @return mixed|string
     * @throws Throwable
     */
    public static function getLazyLoadResult()
    {
        $widget = new self(['lazyLoad' => false]);
        return $widget->renderItems([]);
    }
}
