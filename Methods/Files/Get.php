<?php
	namespace Me\Korolevsky\Api\Methods\Files;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class Get implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "files.get",
				function: [$this, 'request'],
				params: ['id'],
				limits: [0,0,0]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$file = $server->findOne('files', 'WHERE `file_id` = ?', [ $params['id'] ]);
			if($file->isNull()) {
				return new Response(200, new ErrorResponse(1, 'Invalid id.'));
			}

			header("Content-type: image/png");
			readfile($file['filename']);
			return new Response(200, new OKResponse(null), false);
		}

	}