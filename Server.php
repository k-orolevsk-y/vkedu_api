<?php
	namespace Me\Korolevsky\Api;

	use Me\Korolevsky\Api\Utils\Response\Response;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;

	/**
	 * Class Server.
	 * Called when the API is called.
	 *
	 * @package Me\Korolevsky\Api
	 */
	class Server {

		private Api $api;

		/**
		 * Server constructor.
		 */
		public function __construct() {
			$this->api = new Api();
			$this->addMethods();

			$dbData = Data::DB_DATA;
			$server = new DB\Server($dbData['host'], $dbData['user'], $dbData['pass'], $dbData['dbname']);

			$this->api->processRequest(
				self::getMethod(),
				$server
			);
		}

		private function addMethods(): void {
			foreach(array_merge(glob('Methods/*.php'), glob('Methods/*/*.php')) as $file) {
				$classname = mb_strcut("Me\Korolevsky\Api\\".str_replace('/', '\\', $file), 0, -4);
				if(class_exists($classname)) {
					new $classname($this->api);
				}
			}
		}

		public static function getMethod(): string {
			$data = Api::getParams();

			$array = (array) explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
			$method = array_pop($array);

			if(@$data['method'] != null && @$data['method'] != @$method && @$method != null) {
				new Response(200, new ErrorResponse(400, 'Invalid request: this method can not be called that way.'));
				return "";
			} elseif($method == null) {
				$_SERVER['REQUEST_URI'] = $data['method'];

				$array = (array) explode('/', explode('?', $_SERVER['REQUEST_URI'])[0]);
				$method = array_pop($array);
			}

			if(mb_strcut($method, strlen($method) -4) == '.php') $method = mb_strcut($method, 0, -4);
			return empty($method) ? "" : $method;
		}

	}