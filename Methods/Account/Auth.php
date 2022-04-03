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

	class Auth implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "account.auth",
				function: [$this, 'request'],
				params: ['login', 'password'],
				limits: [3, 10, 20],
				need_authorization: false
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$user = $server->findOne('users', 'WHERE `login` = ? OR UPPER(`nickname`) = ?', [ $params['login'], mb_strtoupper($params['login']) ]);
			if($user->isNull()) {
				return new Response(200, new ErrorResponse(1, 'User not registered.'));
			} elseif(!password_verify($params['password'], $user['password'])) {
				return new Response(200, new ErrorResponse(2, "Password is incorrect."));
			}

			$access_token = Authorization::getAccessToken($server, $user['id'])['token'];
			return new Response(200, new OKResponse($access_token));

		}

	}