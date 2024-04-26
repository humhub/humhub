<?php

use humhub\libs\Iso3166Codes;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use yii\db\Expression;
use yii\db\Migration;

/**
 * Fix for https://github.com/humhub/humhub/issues/6919
 *
 * Class m240419_095931_fix_user_profile_country_code
 */
class m240419_095931_fix_user_profile_country_code extends Migration
{
    private ?array $translatedCountries = null;

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        if (!ProfileField::find()->where(['internal_name' => 'country'])->exists()) {
            return; // don't run the migration
        }

        $profiles = Profile::find()
            ->select('country')
            ->distinct('country')
            ->where(['NOT IN', 'country', array_keys(Iso3166Codes::$countries)])
            ->andWhere(['IS NOT', 'country', new Expression('NULL')]);

        foreach ($profiles->column() as $wrongCountryCode) {
            Profile::updateAll(
                ['country' => $this->getCodeByCountry($wrongCountryCode)],
                ['country' => $wrongCountryCode],
            );
        }
    }

    private function getCodeByCountry($wrongCountryCode): ?string
    {
        if ($this->translatedCountries === null) {
            $this->translatedCountries = [];
            foreach (Iso3166Codes::$countries as $code => $title) {
                $this->translatedCountries[$code] = [];
                foreach (Yii::$app->params['availableLanguages'] as $language => $name) {
                    $this->translatedCountries[$code][] = Locale::getDisplayRegion('_' . $code, $language);
                }
            }
        }

        foreach ($this->translatedCountries as $code => $titles) {
            if (in_array($wrongCountryCode, $titles, true)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240419_095931_fix_user_profile_country_code cannot be reverted.\n";

        return false;
    }
}
