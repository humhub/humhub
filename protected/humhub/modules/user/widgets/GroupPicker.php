<?php

namespace humhub\modules\user\widgets;

use humhub\modules\admin\models\forms\UserEditForm;
use humhub\modules\ui\form\widgets\MultiSelect;
use humhub\modules\user\models\forms\EditGroupForm;
use humhub\modules\user\models\Group;
use Yii;

class GroupPicker extends MultiSelect
{
    public string $groupType = EditGroupForm::TYPE_NORMAL;

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
            $groups = Group::find();
            if (!$this->model->isNewRecord) {
                $groups->andWhere(['!=', 'id', $this->model->id]);
            }
            if ($this->groupType === EditGroupForm::TYPE_SUBGROUP) {
                $groups->andWhere(['parent_group_id' => null]);
            }
            $this->items = UserEditForm::getGroupItems($groups->all());
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
