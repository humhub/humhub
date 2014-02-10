Settings
========

The ``ZSetting`` can be used to store settings or configuration options for your
module.

``ZSetting::Set`` and ``ZSetting::Get`` only supports strings with a maximum length
of 255 characters. If you need to store larger texts you can use the ``ZSetting::GetText`` and
``ZSetting::SetText`` methods.

Example:

        // Store a Setting
        ZSetting::Set('nameOfSetting', 'someValue', 'yourModuleId');

        // Get a Setting
        $value = ZSetting::Get('nameOfSetting', 'yourModuleId');


