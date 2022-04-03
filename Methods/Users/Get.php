<?php
	namespace Me\Korolevsky\Api\Methods\Users;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\Data;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class Get implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "users.get",
				function: [$this, 'request'],
				limits: [5, 250, 500]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			if(!empty($params['id'])) {
				$user = $server->findOne('users', 'WHERE `id` = ? OR UPPER(`nickname`) = ?', [ $params['id'], mb_strtoupper($params['id']) ]);
			} else {
				$user_id = Authorization::getUserId($server, $params['access_token']);
				$user = $server->findOne('users', 'WHERE `id` = ?', [ $user_id ]);
			}

			if($user->isNull()) {
				return new Response(200, new ErrorResponse(1, 'Invalid user id.'));
			}

			$user['photo'] = Data::URL.($server->findOne('files', 'WHERE `file_id` = ?', [ $user['photo_id'] ])['filename'] ?? "Files/default.png");
			$user['newsfeed_count'] = $server->count('newsfeed', 'WHERE `user_id` = ?', [ $user['id'] ]);
			$user['likes_count'] = $server->count('likes', 'WHERE `user_id` = ?', [ $user['id'] ]);
			unset($user['reg_ip']);
			unset($user['login']);
			unset($user['password']);

			return new Response(200, new OKResponse($user->getArrayCopy()));
		}

	}