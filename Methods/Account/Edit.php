<?php
	namespace Me\Korolevsky\Api\Methods\Account;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class Edit implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "account.edit",
				function: [$this, 'request'],
				params: ['nickname'],
				limits: [1, 0, 10]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			if(preg_match('/^[A-Za-z][A-Za-z0-9_]{5,14}$/', $params['nickname']) === false) {
				return new Response(200, new ErrorResponse(1, 'Invalid nickname.'));
			}

			$user = $server->findOne('users', 'WHERE UPPER(`nickname`) = ?', [ mb_strtoupper($params['nickname']) ]);
			if(!$user->isNull()) {
				return new Response(200, new ErrorResponse(2, "This nickname is taken."));
			}
			$user_id = Authorization::getUserId($server, $params['access_token']);

			$user = $server->findOne('users', 'WHERE `id` = ?', [ $user_id ]);
			$user['nickname'] = $params['nickname'];
			$server->store($user);

			$user = $user->getArrayCopy();
			$user['id'] = $user_id;
			$user['photo'] = "https://ssapi.ru/vkedu/".($server->findOne('files', 'WHERE `file_id` = ?', [ $user['photo_id'] ])['filename'] ?? "Files/default.png");
			$user['newsfeed_count'] = $server->count('newsfeed', 'WHERE `user_id` = ?', [ $user['id'] ]);
			$user['likes_count'] = $server->count('likes', 'WHERE `user_id` = ?', [ $user['id'] ]);
			unset($user['reg_ip']);
			unset($user['login']);
			unset($user['password']);

			return new Response(200, new OKResponse($user));
		}

	}