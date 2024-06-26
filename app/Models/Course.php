<?php
namespace Rytisluko\Models;

use TypeRocket\Models\WPPost;

class Course extends WPPost
{
    public const POST_TYPE = 'course';

    public function users() {
        
        global $wpdb;

        return $this->belongsToMany(User::class, "{$wpdb->prefix}users_courses", 'user_id', 'course_id');
    }
}