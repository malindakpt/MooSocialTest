<?php
/**
 * Minify Controller
 *
 * @package	Minify.Controller
 */
class MinifyController extends Controller {

/**
 * Index method.
 *
 * @return void
 */
	public function index($type) {
		$name = $_GET['f'];
		if ($name)
		{
			$minifyModel = MooCore::getInstance()->getModel("Minify.MinifyUrl");
			$minify = $minifyModel->getMinify($name);
			if ($minify)
			{
				$paths = json_decode($minify['MinifyUrl']['url'],true);
				$_GET['f'] = implode(',', $paths);
			}
		}

		if (!empty($this->request->base)) {
			$this->_adjustFilenames();
		}

		App::import('Vendor', 'Minify.minify/index');

		$this->response->statusCode('304');
		exit();
	}

	private function _adjustFilenames() {
		$baseUrl = substr($this->request->base, 1) . '/';
		$baseLen = strlen($baseUrl);
		$files = explode(',', $_GET['f']);
		foreach ($files as &$file) {
			if (!strncmp($file, $baseUrl, $baseLen)) {
				$file = substr($file, $baseLen);
			}
		}
		$_GET['f'] = implode(',', $files);
	}
}
?>