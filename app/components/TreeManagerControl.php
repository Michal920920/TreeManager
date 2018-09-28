<?php

namespace App\Components;

use App\Model\TreeManagerService;
use Nette\Application\UI;
use Tracy\Debugger;
use Tracy;

class TreeManagerControl extends UI\Control {

	/** @var TreeManagerService @inject */
	public $treeManagerService;
       
	public function render() {
                if(!isset($this->template->tree)){  
                    $this->template->tree = $this->treeManagerService->displayTree();
                }
		$this->template->setFile(__DIR__ . '/TreeManagerControl.latte');
		$this->template->render();
	}
      /**
       * Přidání uzlu dle parametru PID
       * @ApiAction
       */
      public function handleAdd($pid, $color = null){
          $this->treeManagerService->addNode($pid, $color);
          $this->redrawControl('tree');
      }
      
      /**
       * Odebrání uzlu dle parametru ID
       * @ApiAction
       */
      public function handleRemove($id){
          $this->treeManagerService->removeNode($id);
          $this->redrawControl('tree');
      }
}
