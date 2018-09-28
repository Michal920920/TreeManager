<?php
/**
 * Created by PhpStorm.
 * User: staniik
 * Date: 02.07.18
 * Time: 18:14
 */

namespace App\Components;

use Nette\DI\Container;

class TreeManagerControlFactory {

	private $container;

	function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * @return TreeManagerControl
	 */
	public function create() {
		return $this->container->getService("treeManagerControl");
	}
}