<?php

namespace App\Presenters;

use App\Components\TreeManagerControlFactory;
use App\Model\TreeManagerService;
use App\Model\UserManager;
use App\Components\TreeManagerControl;

class HomepagePresenter extends BasePresenter {

	/** @var TreeManagerControlFactory @inject */
	public $treeManagerControlFactory;

	public $user;

	public function actionDefault() {

	}

	/**
	 * TreeManager
	 * @return TreeManagerControl
	 */
	protected function createComponentTreeManager() {
		$control = $this->treeManagerControlFactory->create();
		return $control;
	}

	public function actionDrop() {

		$control = $this->getComponent('treeManager');
		$control->drop();
		$this->redirect('Homepage:');
	}
}