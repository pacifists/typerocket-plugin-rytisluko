<?php
namespace Rytisluko\Tags;

class MagickLink_Tag extends \Elementor\Core\DynamicTags\Tag {

	public function get_name() {
		return 'magiclink-tag';
	}

	public function get_title() {
		return __( 'XP Magick Link', 'elementor-pro' );
	}

	public function get_group() {
		return 'custom-dynamic-tags';
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::URL_CATEGORY ];
	}

	protected function _register_controls() {


	}

	public function render() 
	{

		if (!empty($_GET['wcf-order'])) 
		{
    		$order_id = $_GET['wcf-order'];
    		$user_id = get_current_user_id();

    		$users_course = \Rytisluko\Models\UsersCourses::new();
    		$users_course->where('user_id', $user_id);
    		$users_course->where('order_id', $order_id);
    		$course_info = $users_course->first();	
    		if (!empty($course_info) && !empty($course_info->magic_link)) 
    		{
    			echo $course_info->magic_link;
    		}
    }
	}
}