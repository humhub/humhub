<template>
    <div class="mb-3" :class="[`field-${fieldId}`, { required }]">
        <label v-if="label" :for="fieldId" class="form-label">{{ label }}</label>
        <input
            ref="input"
            :id="fieldId"
            :name="fieldName"
            :type="type"
            class="form-control"
            :class="{ 'is-invalid': hasError }"
            :placeholder="placeholder"
            :disabled="isDisabled"
            :aria-required="required ? 'true' : null"
            :aria-invalid="hasError ? 'true' : null"
            :aria-describedby="describedBy"
            v-model="internalValue"
        >
        <div v-if="hint" :id="hintId" class="form-text text-muted">{{ hint }}</div>
        <div v-if="hasError" :id="errorId" class="invalid-feedback">
            <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
/**
 * A single-line text input, part of the `HumHubForm` suite (see `HumHubForm.vue`'s
 * own docblock for the suite overview and `docs/develop/ui-js-vuejs-forms.md` for
 * the full reference). Covers `text`/`email`/`password`/`number` via the `type`
 * prop — Yii's own `ActiveField` has no dedicated component per input type either;
 * the HTML `type` attribute is what actually changes browser behavior.
 *
 * ## Markup parity with `yii\bootstrap5\ActiveField`
 *
 * Reference markup (verified against `protected/vendor/yiisoft/yii2-bootstrap5/src/
 * ActiveField.php` + `protected/vendor/yiisoft/yii2/widgets/ActiveField.php`, the
 * base class HumHub's own `humhub\widgets\form\ActiveField` extends without
 * overriding any of the below — see that file, `protected/humhub/widgets/form/
 * ActiveField.php` — the field-container/template/class defaults are pure
 * Bootstrap5 upstream):
 *
 * ```html
 * <div class="mb-3 field-<id>[ required]">
 *     <label class="form-label" for="<id>">Label</label>
 *     <input type="text" id="<id>" class="form-control[ is-invalid]" name="Model[attribute]">
 *     <div class="form-text text-muted">hint</div>
 *     <div class="invalid-feedback">message</div>
 * </div>
 * ```
 * — field container `$options` default (`ActiveField::$options`, bootstrap5), template
 * order `{label}\n{input}\n{hint}\n{error}` (`yii\widgets\ActiveField::$template`,
 * never overridden for the default/vertical layout), `errorCssClass = 'is-invalid'`
 * + `validationStateOn = 'input'` (`yii\bootstrap5\ActiveForm`), `requiredCssClass =
 * 'required'` added to the container when the attribute `isAttributeRequired()`
 * (`ActiveField::begin()`) — reproduced here via the `required` prop (visual marker
 * only, see `form/fieldMixin.js`'s own docblock).
 *
 * `id`/`name` follow the exact same Yii convention (`Html::getInputName()` /
 * `Html::getInputIdByName()`) via `fieldName`/`fieldId` — see `form/fieldMixin.js`
 * and `form/formContext.js`'s `toInputId()`.
 *
 * **One deliberate deviation:** a real `ActiveField`'s `{error}` only ever shows the
 * attribute's FIRST error (`Html::error()` calls `$model->getFirstError()`). This
 * component renders EVERY message for the attribute (`errorMessages`, looped) —
 * because the 422 payload it reads from (`Model::getErrors()`, one array per
 * attribute) can legitimately carry more than one failed rule, and hiding all but
 * the first would silently drop information a live client round-trip actually has
 * available, unlike a same-request server render.
 *
 * @since 1.20
 */
import fieldMixin from './form/fieldMixin.js';

export default {
    mixins: [fieldMixin],
    props: {
        modelValue: { type: String, default: '' },
        type: { type: String, default: 'text' },
    },
    emits: ['update:modelValue'],
    computed: {
        internalValue: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
                this.clearOwnError();
            },
        },
    },
    methods: {
        focus() {
            if (this.$refs.input) {
                this.$refs.input.focus();
            }
        },
    },
};
</script>
