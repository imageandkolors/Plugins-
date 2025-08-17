<?php

/**
 * Fired during plugin activation.
 *
 * @link       https://example.com/
 * @since      1.2.0
 *
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.2.0
 * @package    Cbt_Exam_Plugin
 * @subpackage Cbt_Exam_Plugin/includes
 * @author     Jules <you@example.com>
 */
class Cbt_Exam_Plugin_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.2.0
     */
    public static function activate() {
        self::setup_roles();

        // Create dashboard page
        $dashboard_page = array(
            'post_title'    => wp_strip_all_tags( 'Exam Dashboard' ),
            'post_content'  => '[cbt_dashboard]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
        );

        // Check if page already exists
        $page_exists = get_page_by_title( 'Exam Dashboard', 'OBJECT', 'page' );

        if ( is_null( $page_exists ) ) {
            $page_id = wp_insert_post( $dashboard_page );
            update_option( 'cbt_exam_dashboard_page_id', $page_id );
        } else {
            // If page exists, just make sure the option is set
            update_option( 'cbt_exam_dashboard_page_id', $page_exists->ID );
        }
    }

    /**
     * Setup the custom roles and capabilities.
     *
     * @since    1.4.0
     */
    public static function setup_roles() {
        // Teacher role
        $teacher_caps = get_role( 'editor' )->capabilities;
        $teacher_caps['manage_exams'] = true;
        $teacher_caps['grade_exams'] = true;
        $teacher_caps['view_exam_reports'] = true;
        add_role( 'cbt_teacher', 'Teacher', $teacher_caps );

        // Parent role
        $parent_caps = get_role( 'subscriber' )->capabilities;
        $parent_caps['view_child_results'] = true;
        add_role( 'cbt_parent', 'Parent', $parent_caps );

        // Add caps to admin
        $admin_role = get_role( 'administrator' );
        $admin_role->add_cap( 'manage_exams' );
        $admin_role->add_cap( 'grade_exams' );
        $admin_role->add_cap( 'view_exam_reports' );
    }
}
