<?php
namespace Rytisluko\Models;

use TypeRocket\Models\WPUser;

class User extends WPUser
{

	public function courses() {
		
		global $wpdb;

		return $this->belongsToMany(RlCourse::class, "{$wpdb->prefix}users_courses", 'course_id', 'user_id');
	}
}