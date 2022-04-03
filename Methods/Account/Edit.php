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
				params: ['first_name', 'last_name'],
				limits: [1, 0, 10]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$first_name_len = iconv_strlen($params['first_name']);
			$last_name_len = iconv_strlen($params['last_name']);

			if($first_name_len < 2 || $last_name_len < 2 || $first_name_len > 25 || $last_name_len > 25) {
				return new Response(200, new ErrorResponse(1, 'Invalid first_name or last_name.'));
			}
			$user_id = Authorization::getUserId($server, $params['access_token']);


			$user = $server->findOne('users', 'WHERE `id` = ?', [ $user_id ]);
			$user['first_name'] = str_replace(' ', '', mb_convert_case($params['first_name'], MB_CASE_TITLE, "UTF-8"));
			$user['last_name'] = str_replace(' ', '', mb_convert_case($params['last_name'], MB_CASE_TITLE, "UTF-8"));
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