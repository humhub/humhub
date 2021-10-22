<?php

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerController;
use humhub\modules\space\assets\SpaceChooserAsset;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\permissions\CreatePrivateSpace;
use humhub\modules\space\permissions\CreatePublicSpace;
use humhub\modules\space\permissions\SpaceDirectoryAccess;
use humhub\modules\user\components\PermissionManager;
use humhub\modules\user\models\Follow;
use Yii;
use yii\db\Query;
use yii\helpers\Html;
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

        $this->configure();
        return parent::beforeRun();
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {
        return $this->render($this->viewName, $this->getViewParams());
    }

    /**
     * @return bool
     */
    protected function canRun()
    {
        return !Yii::$app->user->isGuest;
    }

    /**
     * Configure widget before run, used to register assets and js config
     */
    protected function configure()
    {
        SpaceChooserAsset::register($this->view);
        $this->view->registerJsConfig('space.chooser', $this->getJsConfigParams());
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     */
    protected function getViewParams()
    {
        return [
            'currentSpace' => $this->getCurrentSpace(),
            'canCreateSpace' => $this->canCreateSpace(),
            'canAccessDirectory' => Yii::$app->user->can(SpaceDirectoryAccess::class),
            'renderedItems' => $this->renderItems(),
            'noSpaceHtml' => $this->getNoSpaceHtml(),
        ];
    }

    /**
     * @return array
     */
    protected function getJsConfigParams()
    {
        return [
            'lazyLoad' => $this->lazyLoad,
            'noSpace' => $this->getNoSpaceHtml(),
            'remoteSearchUrl' => Url::to(['/space/browse/search-json']),
            'lazySearchUrl' => Url::to(['/space/browse/search-lazy']),
            'text' => [
                'info.remoteAtLeastInput' => Yii::t('SpaceModule.chooser', 'To search for other spaces, type at least {count} characters.', ['count' => 2]),
                'info.emptyOwnResult' => Yii::t('SpaceModule.chooser', 'No member or following spaces found.'),
                'info.emptyResult' => Yii::t('SpaceModule.chooser', 'No result found for the given filter.'),
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getNoSpaceHtml()
    {
        $html = '<i class="fa fa-dot-circle-o"></i><br>' . Yii::t('SpaceModule.chooser', 'My spaces') . '<b class="caret"></b>';
        return Html::tag('div', $html, ['class' => 'no-space']);
    }

    /**
     * @param string $output
     * @return mixed|string
     * @throws \Throwable
     */
    protected function renderItems($output = '')
    {
        if ($this->lazyLoad) {
            return $output;
        }

        // render membership items
        foreach ($this->getMemberships() as $membership) {
            $result = SpaceChooserItem::widget([
                'space' => $membership->space, 'updateCount' => $membership->countNewItems(), 'isMember' => true
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
     * @throws \Throwable
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
     * @throws \Throwable
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
     * @throws \yii\base\InvalidConfigException
     */
    protected function canCreateSpace()
    {
        /** @var PermissionManager $manager */
        $manager = Yii::$app->user->permissionmanager;
        return $manager->can(new CreatePublicSpace) || $manager->can(new CreatePrivateSpace());
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
     * Returns the membership query
     *
     * @return Query
     * @deprecated since version 1.2
     */
    protected function getMembershipQuery()
    {
        $query = Membership::find()->joinWith('space')
            ->where(['space_membership.user_id' => Yii::$app->user->id, 'space_membership.status' => Membership::STATUS_MEMBER]);

        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $query->orderBy('name ASC');
        } else {
            $query->orderBy('last_visit DESC');
        }

        return $query;
    }

    /**
     * @param Space $space
     * @param bool $withChooserItem
     * @param array $itemOptions
     * @return array
     * @throws \Exception
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
     * @throws \Throwable
     */
    public static function getLazyLoadResult()
    {
        $widget = new self(['lazyLoad' => false]);
        return $widget->renderItems([]);
    }
}
