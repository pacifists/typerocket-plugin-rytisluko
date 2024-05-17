<?php
namespace Rytisluko;

use TypeRocket\Core\System;
use TypeRocket\Utility\Helper;
use TypeRocket\Pro\Register\BasePlugin;
use Rytisluko\Models;
use Rytisluko\View;

class RytislukoTypeRocketPlugin extends BasePlugin
{
    protected $title = 'Rytisluko';
    protected $slug = 'rytisluko';
    protected $migrationKey = 'rytisluko_migrations';
    protected $migrations = true;

    public function init()
    {
        // Plugin Settings
        $page = $this->pluginSettingsPage([
            'view' => View::new('settings', [
                'form' => Helper::form()->setGroup('rytisluko_settings')->useRest()
            ])
        ]);

        $this->inlinePluginLinks(function() use ($page) {
            return [
                'settings' => "<a href=\"{$page->getUrl()}\" aria-label=\"Settings\">Settings</a>"
            ];
        });

        // Assets Manifest
        $manifest = $this->manifest('public');
        $uri = $this->uri('public');

        // Front Assets
        add_action('wp_enqueue_scripts', function() use ($manifest, $uri) {
            wp_enqueue_style( 'main-style-' . $this->slug, $uri . $manifest['/front/front.css'] );
            wp_enqueue_script( 'main-script-' . $this->slug, $uri . $manifest['/front/front.js'], [], false, true );
        });

        // Admin Assets
        add_action('admin_enqueue_scripts', function() use ($manifest, $uri) {
            wp_enqueue_style( 'admin-style-' . $this->slug, $uri . $manifest['/admin/admin.css'] );
            wp_enqueue_script( 'admin-script-' . $this->slug, $uri . $manifest['/admin/admin.js'], [], false, true );
        });

        // TODO: Add your init code here

        // Setup Courses
        $course = tr_post_type('Course');
        $course->setIcon('dashicons-groups');
        $course->setSupports(['title', 'thumbnail']);
        $course->setTitlePlaceholder( 'Enter course name here' );
        $course->setRest();

        tr_meta_box('Xperiencify Course')->apply($course)->setCallback(function() {
            $form = tr_form();
            echo $form->text('XP Course ID');
        });

        $course->addColumn('XP Course ID');

        // hooking up to WC Products and adding associated course selection for product

        $product = tr_post_type('Product');
        tr_meta_box('Related courses')->apply($product)->setCallback(function() {
            $form = tr_form();
            echo $form->search('Course ID')->setLabel('Select Course')->multiple()->setPostTypeOptions('Course');
        });

        $settings = get_option('rytisluko_settings');
        // wp_die(json_encode($settings['xperiencify_api_key']));
        if (!empty($settings['xperiencify_api_key'])) {
            add_action('woocommerce_checkout_order_processed', '\Rytisluko\RytislukoTypeRocketPlugin::xp_enroll_student', 10, 1);    
        }

        // adding Elementor custom Dynamic Tag to use on Thank you page to redirect Students to their course
        // not sure how to check if Elementor is loaded properly, so does not matter for my use case
        // if Elementor is not used then this can throw an error
        add_action( 'elementor/dynamic_tags/register_tags', [ $this, 'register_magic_link_tag' ] );

    }

    public static function xp_enroll_student($order_id) 
    {

        $settings = get_option('rytisluko_settings');
        $api_key = $settings['xperiencify_api_key'];

        // Getting an instance of the order object
        $order = wc_get_order($order_id);

        // iterating through each order items (getting product ID and the product object) 
        // (work for simple and variable products)
        foreach ( $order->get_items() as $item_id => $item ) {

            if( $item['variation_id'] > 0 ){
                $product_id = $item['variation_id']; // variable product
            } else {
                $product_id = $item['product_id']; // simple product
            }

            // Get the product object
            $product = wc_get_product( $product_id );
            $course_ids = get_post_meta($product_id, 'course_id');
            if (!empty($course_ids)) {
                foreach($course_ids[0] as $course_id) {
                    // get the related course info
                    $course = \Rytisluko\Models\Course::new()->with('meta')->findById($course_id);
                    $user_id = $order->get_customer_id();

                    // now need to create connection User -> Course
                    // checking if this course and user already exist for some reason
                    $users_course = \Rytisluko\Models\UsersCourses::new();
                    $users_course->where('course_id', $course_id);
                    $users_course->where('user_id', $user_id);
                    $users_course->where('order_id', $order_id);
                    $course_info = $users_course->first();

                    if (empty($course_info)) {
                        $users_course->course_id = $course_id;
                        $users_course->user_id = $order->get_customer_id();
                        $users_course->order_id = $order_id;
                        $users_course_id = $users_course->create(); 
                    }
                    else {
                        $users_course_id = $course_info->id;
                    }

                    // now need to add new student to xperiencify course

                    $magic_link = self::create_xp_student($api_key, $order, $course);
                    if(!empty($magic_link)) {
                        $update_object = \Rytisluko\Models\UsersCourses::new()->findById($users_course_id);
                        $update_object->magic_link = $magic_link;
                        $update_object->update();
                    }

                }
            }
        }
    }

    static function create_xp_student($api_key, $order, $course) 
    {
        $api_url="https://api.xperiencify.io/api/public/student/create/?api_key={$api_key}";   // Available from your Account page

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);

        $data = array(
          'student_email' => $order->get_billing_email(),
          'course_id' => $course->meta->xp_course_id, // Course ID is in the URL of your course edit page
          'first_name' => $order->get_billing_first_name(),
          'last_name' => $order->get_billing_last_name(),  // Optional
          'phone' => $order->get_billing_phone(),  // Optional
        );

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $output = curl_exec($ch);

        // Everything after here is optional
        // Check for errors
        if (curl_errno($ch)) {
          $error_msg = curl_error($ch);
        }
        // Handle any possible errors
        if (isset($error_msg)) {
            \TypeRocket\Pro\Utility\Log::error("XP Student creation failed");
            \TypeRocket\Pro\Utility\Log::error($error_msg);
          return false;
        } else {
            $magic_link = "";
            $output_array = json_decode($output, true);
            if (isset($output_array['magic_link'])) {
                $magic_link=$output_array['magic_link'];
            }
            else {
                \TypeRocket\Pro\Utility\Log::error(json_encode($output_array));
            }

            return $magic_link;
        }
    }

    public function register_magic_link_tag ($dynamic_tags) 
    {
        // Register group
        \Elementor\Plugin::$instance->dynamic_tags->register_group( 'custom-dynamic-tags', [
            'title' => 'My Custom Dynamic Tags'
        ] );

        // Include the Dynamic tag class file
        include_once( 'Tags/Magicklink_Tag.php' );

        // Finally register the tag
        $dynamic_tags->register_tag( 'Rytisluko\Tags\MagickLink_Tag' );

    }

    public function routes()
    {
        // TODO: Add your TypeRocket route code here
    }

    public function policies()
    {
        // TODO: Add your TypeRocket policies here
        return [

        ];
    }

    public function activate()
    {
        $this->migrateUp();
        System::updateSiteState('flush_rewrite_rules');

        // TODO: Add your plugin activation code here
    }

    public function deactivate()
    {
        // Migrate `down` only on plugin uninstall
        System::updateSiteState('flush_rewrite_rules');

        // TODO: Add your plugin deactivation code here
    }

    public function uninstall()
    {
        $this->migrateDown();

        // TODO: Add your plugin uninstall code here
    }
}