<?php
	namespace Me\Korolevsky\Api\Methods\Newsfeed;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Interfaces\Method;
	use Me\Korolevsky\Api\Utils\Authorization;
	use Me\Korolevsky\Api\Utils\Response\ErrorResponse;
	use Me\Korolevsky\Api\Utils\Response\OKResponse;
	use Me\Korolevsky\Api\Utils\Response\Response;

	class Like implements Method {

		public function __construct(Api $api) {
			$api->addMethod(
				method: "newsfeed.like",
				function: [$this, 'request'],
				params: ['post_id'],
				limits: [4, 100, 200]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			$newsfeed = $server->findOne('newsfeed', 'WHERE `id` = ?', [ $params['post_id'] ]);
			if($newsfeed->isNull()) {
				return new Response(200, new ErrorResponse(1, "Invalid post_id."));
			}
			$user_id = Authorization::getUserId($server, $params['access_token']);

			$like = $server->findOne('likes', 'WHERE `newsfeed_id` = ? AND `user_id` = ?', [ $newsfeed['id'], $user_id ]);
			if($like->isNull()) {
				$like = $server->dispense('likes');
				$like['newsfeed_id'] = $newsfeed['id'];
				$like['user_id'] = $user_id;
				$server->store($like);

				return new Response(200, new OKResponse(true));
			}

			$server->trash($like);
			return new Response(200, new OKResponse(false));
		}

	}