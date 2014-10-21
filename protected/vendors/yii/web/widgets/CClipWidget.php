<?php
/**
 * CClipWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CClipWidget records its content and makes it available elsewhere.
 *
 * Content rendered between its {@link init()} and {@link run()} calls are saved
 * as a clip in the controller. The clip is named after the widget ID.
 *
 * See {@link CBaseController::beginClip} and {@link CBaseController::endClip}
 * for a shortcut usage of CClipWidget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CClipWidget extends CWidget
{
	/**
	 * @var boolean whether to render the clip content in place. Defaults to false,
	 * meaning the captured clip will not be displayed.
	 */
	public $renderClip=false;

	/**
	 * Starts recording a clip.
	 */
	public function init()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Ends recording a clip.
	 * This method stops output buffering and saves the rendering result as a named clip in the controller.
	 */
	public function run()
	{
		$clip=ob_get_clean();
		if($this->renderClip)
			echo $clip;
		$this->getController()->getClips()->add($this->getId(),$clip);
	}
}