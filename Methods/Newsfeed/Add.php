<?php
	namespace Me\Korolevsky\Api\Methods\Newsfeed;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;

	class Add implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "newsfeed.add",
				function: [$this, 'request'],
				params: ['text'],
				limits: [3, 10, 20]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$text_len = iconv_strlen($params['text']);
			if($text_len < 6 || $text_len > 4096) {
				return new Response(200, new ErrorResponse(1, "Text invalid."));
			}

			if($params['files'] != null) {
				$files = explode(',', str_replace([ '', ';' ], '', $params['files']));
				foreach($files as $key => $file) {
					$file = $server->findOne('files', 'WHERE `file_id` = ?', [ $file ]);
					if($file->isNull()) {
						unset($files[$key]);
					}
				}
			} else {
				$files = null;
			}

			$newsfeed = $server->dispense('newsfeed');
			$newsfeed['user_id'] = Authorization::getUserId($server, $params['access_token']);
			$newsfeed['time'] = time();
			$newsfeed['text'] = $params['text'];
			$newsfeed['files'] = $files == null ? null : implode(',', $files);
			$newsfeed['views'] = 0;
			$server->store($newsfeed);

			$newsfeed = $newsfeed->getArrayCopy();
			if($newsfeed['files'] != null) {
				$newsfeed['files'] = explode(', ', $newsfeed['files']);
			}

			return new Response(200, new OKResponse($newsfeed));
		}

	}