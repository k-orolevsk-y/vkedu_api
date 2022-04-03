<?php
	namespace Me\Korolevsky\Api\Interfaces;

	use Me\Korolevsky\Api\Api;
	use Me\Korolevsky\Api\DB\Server;
	use Me\Korolevsky\Api\DB\Servers;
	use Me\Korolevsky\Api\Utils\Response\Response;

	interface Method {

		public function __construct(Api $api);
		public function request(Servers|Server $server, array $params): Response;

	}