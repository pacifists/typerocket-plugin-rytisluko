<?php
namespace Rytisluko\Models;

use TypeRocket\Models\WPPost;

class RlCourse extends WPPost
{

    public function users() {
        
        global $wpdb;

        return $this->belongsToMany(User::class, "{$wpdb->prefix}users_courses", 'user_id', 'course_id');
    }
}