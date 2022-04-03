<?php
	namespace Me\Korolevsky\Api\Methods\Account;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class ExitSession implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "account.exitSession",
				function: [$this, 'request'],
				limits: [0,0,0]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$user_id = Authorization::getUserId($server, $params['access_token']);

			$access_token = $server->findOne('access_tokens', 'WHERE `user_id` = ?', [ $user_id ]);
			if(!$access_token->isNull()) {
				$server->trash($access_token);
			}

			return new Response(200, new OKResponse(true));
		}

	}