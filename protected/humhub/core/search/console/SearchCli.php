<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Console tool for search
 *
 * @package humhub.modules_core.search.console
 * @since 0.12
 */
class SearchCli extends HConsoleCommand
{

    public function init()
    {
        $this->printHeader('Search Console');
        return parent::init();
    }

    public function actionOptimize()
    {
        print "Optimizing search index: ";
        Yii::app()->search->optimize();
        print "OK!\n\n";
    }

    public function actionRebuild()
    {
        print "Rebuild search index: ";
        Yii::app()->search->rebuild();
        print "OK!\n\n";
    }

    public function actionFind($args, $page = 1, $pageSize = 5, $model = "")
    {

        if (!isset($args[0])) {
            print "Error: Keyword parameter required!\n\n";
            print $this->getHelp();
            return;
        }

        $keyword = $args[0];

        print "Searching for: " . $keyword . " \n";

        $results = Yii::app()->search->find($keyword, [
            'pageSize' => $pageSize,
            'page' => $page,
            'model' => ($model != "") ? explode(",", $model) : null
        ]);

        print_r($results);
    }

    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic search [action] [parameter]

DESCRIPTION
  This command provides console support for search. 

EXAMPLES
 * yiic search find [keyword]
   Searches index for given keyword

 * yiic search rebuild
   Rebuilds search index

 * yiic search optimize
   Optimizes the search index

EOD;
    }

}
