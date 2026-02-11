<?php

namespace BoltAudit\App\Http\Controllers;

use BoltAudit\App\Repositories\DatabaseRepository;
use BoltAudit\WpMVC\Routing\Response;
use Exception;

class DatabaseController extends Controller {
        public function index() {
                try {
                        $response = DatabaseRepository::get_all();

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
