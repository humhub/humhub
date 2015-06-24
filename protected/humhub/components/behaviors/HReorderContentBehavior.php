<?php
/**
 * This behavior contains the logic of reordering database items according to an array of items given in the request's parameters..
 * 
 * @author Sebastian Stumpf
 *
 */
class HReorderContentBehavior extends CBehavior {
	
	/**
	 * Changes the order of items in the database. The items to change have to be set to the requests parameters as an array 'items'.
	 * Each item contains its 'id' and its new 'index'.
	 * The methods parameters can be used for further configuration.
	 * 
	 * @param string $modelName (required) the name of the items model class.
	 * @param number $statusCode (required) the status code. If set to >= 400, the method just generates an error response, without even trying to save the items new order. (Use this, if a validation error occurred for example.) 
	 * @param string $message (optional) set the message that should be set to the responses value. May be overwritten if an error occurs in the method. 
	 * @param string $idName (optional) the name of the items id column in the table.
	 * @param string $sortOrderName (optional) the name of the items sort order column in the table.
	 * @return multitype:string an array with a 'type' and a 'value' key.
	 * 
	 */
	public function reorderContent($modelName = null, $statusCode = 200, $message = '', $idName = 'id', $sortOrderName = 'sort_order') {
		
		$response = array();
		
		// There already occurred an error in the validation -> generate the error json response
		if($statusCode >= 400) {
			$response['type'] = 'error';
			$response['value'] = $message;
			http_response_code($statusCode);
			return $response;
		}
		
		// check if the required model name is null or empty
		if($modelName == null || $modelName == '') {
			$response['type'] = 'error';
			$response['value'] = 'The model is not defined.';
			http_response_code(403);
			return $response;
		}
		// load items from the request parameters
		$items = Yii::app()->request->getParam('items');
		// check if items are properly set
		if($items == null || !is_array($items) || empty($items)) {
			$response['type'] = 'error';
			$response['value'] = 'The array of items to reorder, given in the request, was empty.';
			http_response_code(403);
			return $response;
		} else {
			foreach ($items as $key => $item) {
				if($item == null || !is_array($items) || empty($items) || !array_key_exists('id', $item) || !array_key_exists('index', $item)) {
					$response['type'] = 'error';
					$response['value'] = 'The item at following index was malformed: '.$key;
					http_response_code(403);
					return $response;
				}
				$model = $modelName::model()->findByAttributes(array($idName => $item['id']));
				if($model == null || $model == '') {
					$response['type'] = 'error';
					$response['value'] = 'The following item could not be found in the database: Model: '.$modelName.', '.$idName.': '.$item['id'].'.';
					http_response_code(403);
					return $response;
				}
				$model->attributes = array($sortOrderName => $item['index']);
				$model->save();
			}
		}
		
		$response['type'] = 'success';
		$response['value'] = $message;
		return $response;
	}
}

?>