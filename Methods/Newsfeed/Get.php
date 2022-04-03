<?php
	namespace Me\Korolevsky\Api\Methods\Newsfeed;

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
				method: "newsfeed.get",
				function: [$this, 'request'],
				limits: [0, 1000, 1500]
			);
		}

		public function request(Server|Servers $server, array $params): Response {
			if(!empty($params['user_id'])) {
				$user = $server->findOne('users', 'WHERE `id` = ?', [ $params['user_id'] ]);
				if($user->isNull()) {
					return new Response(200, new ErrorResponse(1, 'Invalid user_id.'));
				} else {
					$newsfeed = $server->select("SELECT * FROM `newsfeed` WHERE `user_id` = ? ORDER BY `time` DESC", [ $params['user_id'] ]);
				}
			} else {
				$newsfeed = $server->select("SELECT * FROM `newsfeed` ORDER BY `time` DESC");
			}

			$profiles = [];
			$user_id = Authorization::getUserId($server, $params['access_token']);

			foreach($newsfeed as $key => $post) {
				if(empty($profiles[$post['user_id']])) {
					$post_user = $server->select('SELECT id,nickname,photo_id FROM `users` WHERE `id` = ?', [ $post['user_id'] ])[0];
					$post_user['photo'] = Data::URL.($server->findOne('files', 'WHERE `file_id` = ?', [ $post_user['photo_id'] ])['filename'] ?? "Files/default.png");;
					$profiles[$post_user['id']] = $post_user;
				}

				if($post['files'] != null) {
					$newsfeed[$key]['files'] = explode(',', $post['files']);
					foreach($newsfeed[$key]['files'] as $file_key => $file) {
						$newsfeed[$key]['files'][$file_key] = Data::URL.$server->findOne('files', 'WHERE `file_id` = ?', [ $file ])['filename'] ?? "default.png";
					}
				}

				$newsfeed[$key]['likes'] = $server->count('likes', 'WHERE `newsfeed_id` = ?', [ $post['id'] ]);
				$newsfeed[$key]['is_liked'] = !$server->findOne('likes', 'WHERE `newsfeed_id` = ? AND `user_id` = ?', [ $post['id'], $user_id ])->isNull();
				$newsfeed[$key]['views'] += 1;

				$post = $server->findOne('newsfeed', 'WHERE `id` = ?', [ $post['id'] ]);
				$post['views'] += 1;
				$server->store($post);
			}

			return new Response(200, new OKResponse([
				'count' => count($newsfeed),
				'items' => $newsfeed,
				'profiles' => $profiles
			]));
		}

	}