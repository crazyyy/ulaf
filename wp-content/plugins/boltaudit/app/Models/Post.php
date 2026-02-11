<?php

namespace BoltAudit\App\Models;

use BoltAudit\WpMVC\App;
use BoltAudit\WpMVC\Database\Eloquent\Model;
use BoltAudit\WpMVC\Database\Eloquent\Relations\HasMany;
use BoltAudit\WpMVC\Database\Resolver;

class Post extends Model {
    public static function get_table_name():string {
        return 'posts';
    }

    public function meta(): HasMany {
        return $this->has_many( PostMeta::class, 'post_id', 'ID' );
    }

    public function resolver():Resolver {
        return App::$container->get( Resolver::class );
    }
}