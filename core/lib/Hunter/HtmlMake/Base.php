<?php

namespace Hunter\Core\HtmlMake;

/**
 * html make class.
 */
class Base {

	/**
	 * Generating static file
	 *
	 * @param string $action
	 * @param array $args
	 * @param $file
	 *
	 * @return bool
	 */
	public function make($action, array $args, $file) {
		$_GET   = array_merge($_GET, $args);
		$info   = explode('::', $action);
		$method = $info[1];
		$data 	= (new $info[0])->$method();
		$file_name = 'sites/html/'.str_replace('.', '/', $file).'.html';

		if(!is_dir(dirname($file_name))) {
			mkdir(dirname($file_name), 0777, true);
		}

		return file_put_contents($file_name, $data) !== false;
	}
}
