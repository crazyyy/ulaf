<?php

namespace BoltAudit\App\Http\Controllers;

use BoltAudit\App\Repositories\PostsRepository;
use BoltAudit\WpMVC\Routing\Response;
use Exception;

class PostController extends Controller {
        public function index() {
                try {
                        $response = PostsRepository::get_all_details();

                        return Response::send( $response );
                } catch ( Exception $e ) {
                        return Response::send(
                                [
                                        'message' => $e->getMessage(),
                                ],
                                500
                        );
                }
        }
}
