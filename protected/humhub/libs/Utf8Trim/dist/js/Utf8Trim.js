function Utf8Trim($form, attribute, options, value) {
    var $input = $form.find(attribute.input);
    if ($input.is(':checkbox, :radio')) {
        return value;
    }

    value = $input.val();
    if (!options.skipOnEmpty || !yii.validation.isEmpty(value)) {
        value = value.replace(/^[\p{Z}\s]+|[\p{Z}\s]+$/gu, ' ').trim();
        $input.val(value);
    }

    return value;
}
