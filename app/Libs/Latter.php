<?php

namespace App\Libs;
use Latte;

class Latter extends Latte\Engine {

	function __construct() {
		parent::__construct();

	    // Define directories, used by Latte:
		is_dir(STORE_DIR .'cache') or mkdir(STORE_DIR .'cache');

		$this->setTempDirectory(STORE_DIR .'cache');
	}

	public function php($vue, $data = array()){
		return $this->render(VIEW_PATH . $vue.'.php', $data);
	}

	public function view_file($vue, $data = array()){
		return $this->render(VIEW_PATH . $vue, $data);
	}

	public function view($vue, $data = array()){
		return $this->render(VIEW_PATH . $vue.'.latte', $data);
	}

	public function html($vue, $data = array()){
		return $this->render(VIEW_PATH . $vue.'.html', $data);
	}

	/**
	 * Une fonction qui retourne le template compilé
	 *
	 * @param string $vue
	 * @param array $data
	 * @return string
	 */
	public function template($vue, $data = array()){
		return $this->renderToString(VIEW_PATH . $vue.'.html', $data);
	}

	/**
	 * Une fonction qui retourne le template compilé
	 *
	 * @param string $vue
	 * @param array $data
	 * @return string
	 */
	public function htmlString($vue, $data = array()){
		return $this->renderToString(VIEW_PATH . $vue.'.latte', $data);
	}


}
