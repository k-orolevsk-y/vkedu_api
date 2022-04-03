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
				params: ['login', 'password', 'nickname'],
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
			} elseif(iconv_strlen($params['password']) <= 6) {
				return new Response(200, new ErrorResponse(3, 'Bad password.'));
			}

			if(preg_match('/^[A-Za-z][A-Za-z0-9_]{5,14}$/', $params['nickname']) === false) {
				return new Response(200, new ErrorResponse(4, 'Invalid nickname.'));
			}

			$user = $server->findOne('users', 'WHERE UPPER(`nickname`) = ?', [ mb_strtoupper($params['nickname']) ]);
			if(!$user->isNull()) {
				return new Response(200, new ErrorResponse(5, "This nickname is taken."));
			}

			$user = $server->dispense('users');
			$user['nickname'] = $params['nickname'];
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