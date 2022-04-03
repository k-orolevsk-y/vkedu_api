<?php
	namespace Me\Korolevsky\Api\Methods\Account;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Ip;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class Register implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "account.register",
				function: [$this, 'request'],
				params: ['login', 'password', 'first_name', 'last_name'],
				limits: [3, 10, 20],
				need_authorization: false
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$user = $server->findOne('users', 'WHERE `login` = ?', [ $params['login'] ]);
			if(!$user->isNull()) {
				return new Response(200, new ErrorResponse(1, 'User with this login is registered.'));
			}

			if(!filter_var($params['login'], FILTER_VALIDATE_EMAIL)) {
				return new Response(200, new ErrorResponse(2, "Bad login."));
			} elseif(iconv_strlen($params['password']) <=
				6) {
				return new Response(200, new ErrorResponse(3, 'Bad password.'));
			}

			$first_name_len = iconv_strlen($params['first_name']);
			$last_name_len = iconv_strlen($params['last_name']);

			if($first_name_len < 2 || $last_name_len < 2 || $first_name_len > 25 || $last_name_len > 25) {
				return new Response(200, new ErrorResponse(4, 'Invalid first_name or last_name.'));
			}

			$first_name = str_replace(' ', '', mb_convert_case($params['first_name'], MB_CASE_TITLE, "UTF-8"));
			$last_name = str_replace(' ', '', mb_convert_case($params['last_name'], MB_CASE_TITLE, "UTF-8"));

			$user = $server->dispense('users');
			$user['first_name'] = $first_name;
			$user['last_name'] = $last_name;
			$user['photo_id'] = 0;
			$user['reg_time'] = time();
			$user['reg_ip'] = Ip::get();
			$user['login'] = $params['login'];
			$user['password'] = password_hash($params['password'], PASSWORD_BCRYPT);
			$server->store($user);


			$access_token = Authorization::getAccessToken($server, $user['id'])['token'];
			return new Response(200, new OKResponse($access_token));
		}

	}