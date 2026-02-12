<?php

namespace BoltAudit\App\Models;

use BoltAudit\WpMVC\App;
use BoltAudit\WpMVC\Database\Eloquent\Model;
use BoltAudit\WpMVC\Database\Resolver;

class PostMeta extends Model {
    public static function get_table_name():string {
        return 'postmeta';
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}