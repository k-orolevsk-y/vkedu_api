<?php
	namespace Me\Korolevsky\Api\Methods\Files;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class Upload implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "files.upload",
				function: [$this, 'request'],
				limits: [5, 100, 150],
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			if($_FILES == null) {
				return new Response(200, new ErrorResponse(1, 'Files is undefined'));
			}

			$uploaded_files = [];
			foreach($_FILES as $file) {
				if(strripos($file['type'], 'image') !== 0) {
					$uploaded_files[] = false;
					continue;
				}

				$filename = 'Files/' .  uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
				if(!move_uploaded_file($file['tmp_name'], $filename)) {
					$uploaded_files[] = false;
					continue;
				}

				$db = $server->dispense('files');
				$db['file_id'] = uniqid();
				$db['user_id'] = Authorization::getUserId($server, $params['access_token']);
				$db['filename'] = $filename;
				$server->store($db);

				$uploaded_files[] = $db['file_id'];
			}

			return new Response(200, new OKResponse($uploaded_files));
		}

	}