<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use Yii;
use yii\helpers\Html;

use yii\web\JsExpression;

/**
 * ShowMore ajax pager
 * 
 * @inheritdoc
 * @since 1.1.1
 * @author luke
 */
class ShowMorePager extends \humhub\widgets\LinkPager
{

    /**
     * @var Pagination the pagination object that this pager is associated with.
     * You must set this property in order to make LinkPager work.
     */
    public $pagination;

    /**
     * AjaxButton widget options
     * 
     * @see AjaxButton
     * @var array 
     */
    public $ajaxButtonOptions = [];

    /**
     * @var string element id
     */
    public $id = 'btnShowMore';

    /**
     * Initializes the pager.
     */
    public function init()
    {
        if ($this->pagination === null) {
            throw new InvalidConfigException('The "pagination" property must be set.');
        }

        if (!isset($this->ajaxButtonOptions['htmlOPtions']['id'])) {
            $this->ajaxButtonOptions['htmlOptions']['id'] = $this->id . '_btn';
        }

        if (!isset($this->ajaxButtonOptions['ajaxOptions']['type'])) {
            $this->ajaxButtonOptions['ajaxOptions']['type'] = 'POST';
        }

        if (!isset($this->ajaxButtonOptions['ajaxOptions']['beforeSend'])) {
            $this->ajaxButtonOptions['ajaxOptions']['beforeSend'] = new JsExpression('function(){ $("#' . $this->ajaxButtonOptions['htmlOptions']['id'] . '").remove(); $("#' . $this->id . '_loader").removeClass("hidden"); }');
        }

        if (!isset($this->ajaxButtonOptions['ajaxOptions']['success'])) {
            $this->ajaxButtonOptions['ajaxOptions']['success'] = new JsExpression('function(html){ $("#globalModal").html(html); }');
        }

        if (!isset($this->ajaxButtonOptions['label'])) {
            $this->ajaxButtonOptions['label'] = Yii::t('base', 'Show more');
        }

        if (!isset($this->ajaxButtonOptions['htmlOptions']['class'])) {
            $this->ajaxButtonOptions['htmlOptions']['class'] = 'btn btn-default';
            $this->ajaxButtonOptions['htmlOptions']['data-ui-loader'] = '1';
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        echo $this->renderMoreButton();
    }

    /**
     * @inheritdoc
     */
    protected function renderMoreButton()
    {
        $pageCount = $this->pagination->getPageCount();
        $currentPage = $this->pagination->getPage() + 1;

        if ($currentPage >= $pageCount) {
            return '';
        }

        if (!isset($this->ajaxButtonOptions['ajaxOptions']['url'])) {
            $this->ajaxButtonOptions['ajaxOptions']['url'] = $this->pagination->createUrl($currentPage);
        }

        $moreButton = AjaxButton::widget($this->ajaxButtonOptions);
        return Html::tag('div', Html::tag('br') . $moreButton . LoaderWidget::widget(['id' => $this->id . '_loader', 'cssClass' => 'hidden']), ['id' => $this->id, 'class' => 'pagination-container']);
    }

}
