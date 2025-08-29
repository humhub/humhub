<?php

namespace humhub\modules\user\widgets;

use humhub\modules\admin\models\forms\UserEditForm;
use humhub\modules\ui\form\widgets\MultiSelect;
use humhub\modules\user\models\forms\EditGroupForm;
use Yii;

class GroupPicker extends MultiSelect
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDefaultGroupFormOptions();
        parent::init();
    }

    private function initDefaultGroupFormOptions(): void
    {
        if (!($this->model instanceof EditGroupForm)) {
            return;
        }

        if (empty($this->items)) {
            $this->items = UserEditForm::getGroupItems();
            if (!$this->model->isNewRecord && isset($this->items[$this->model->id])) {
                unset($this->items[$this->model->id]);
            }
        }

        if (!isset($this->options['data-tags'])) {
            $this->options['data-tags'] = 'false';
        }

        $this->placeholderMore = match ($this->attribute) {
            'subgroups' => Yii::t('AdminModule.user', 'Add Subgroup(s)'),
            'parent' => Yii::t('AdminModule.user', 'Add Parent Group'),
        };

        if ($this->attribute === 'parent') {
            $this->maxSelection = 1;
        }

        if (($this->model->type === EditGroupForm::TYPE_NORMAL && $this->attribute === 'parent')
            || ($this->model->type === EditGroupForm::TYPE_SUBGROUP && $this->attribute === 'subgroups')) {
            $this->field->options['class']['group_type'] = 'd-none';
        }
    }
}
