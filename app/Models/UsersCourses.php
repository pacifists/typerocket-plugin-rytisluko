<?php
namespace Rytisluko\Models;

use TypeRocket\Models\Model;

class UsersCourses extends Model
{
    protected $resource = 'users_courses';

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course() {
        return $this->belongsTo(RlCourse::class, 'course_id');
    }
}