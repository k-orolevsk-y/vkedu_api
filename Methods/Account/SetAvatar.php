<?php
	namespace Me\Korolevsky\Api\Methods\Account;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\Response;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;

	class SetAvatar implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "account.setAvatar",
				function: [$this, 'request'],
				params: ['file_id'],
				limits: [0, 0, 10]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$user_id = Authorization::getUserId($server, $params['access_token']);

			$file = $server->findOne('files', 'WHERE `file_id` = ? AND `user_id` = ?', [ $params['file_id'], $user_id ]);
			if($file->isNull()) {
				return new Response(200, new ErrorResponse(1, 'Invalid file_id.'));
			}

			$user = $server->findOne('users', 'WHERE `id` = ?', [ $user_id ]);
			$user['photo_id'] = $file['file_id'];
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