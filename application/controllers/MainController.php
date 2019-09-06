<?php

namespace application\controllers;

use application\core\Controller;

class MainController extends Controller {

	public function indexAction() {
		$result = $this->model->initiateBattle();

		$vars = [
			'battle' => $result,
		];

		$this->view->render('SM Slider', $vars);
	}

}