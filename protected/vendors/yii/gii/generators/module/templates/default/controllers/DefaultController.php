<?php echo "<?php\n"; ?>

class DefaultController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}
}