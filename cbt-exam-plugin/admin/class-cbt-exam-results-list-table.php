<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CBT_Exam_Results_List_Table extends WP_List_Table {

    private $exam_id;

    public function __construct( $exam_id ) {
        $this->exam_id = $exam_id;
        parent::__construct( [
            'singular' => __( 'Result', 'cbt-exam-plugin' ),
            'plural'   => __( 'Results', 'cbt-exam-plugin' ),
            'ajax'     => false
        ] );
    }

    public function get_columns() {
        return [
            'student'     => __( 'Student', 'cbt-exam-plugin' ),
            'score'       => __( 'Score', 'cbt-exam-plugin' ),
            'percentage'  => __( 'Percentage', 'cbt-exam-plugin' ),
            'status'      => __( 'Status', 'cbt-exam-plugin' ),
            'date'        => __( 'Date', 'cbt-exam-plugin' ),
        ];
    }

    public function prepare_items() {
        $this->_column_headers = array( $this->get_columns(), array(), array() );

        $this->items = $this->get_results_data();
    }

    private function get_results_data() {
        return get_posts( [
            'post_type' => 'cbt_result',
            'numberposts' => -1,
            'meta_key' => '_cbt_result_exam_id',
            'meta_value' => $this->exam_id,
        ] );
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'date':
                return get_the_date( '', $item );
            default:
                return get_post_meta( $item->ID, '_cbt_result_' . $column_name, true );
        }
    }

    public function column_student( $item ) {
        $user = get_userdata( $item->post_author );
        $actions = array(
            'view_submission' => sprintf( '<a href="?page=%s&action=%s&result_id=%s">View Submission</a>', $_REQUEST['page'], 'view_submission', $item->ID ),
        );
        return $user->display_name . $this->row_actions( $actions );
    }

    public function column_score( $item ) {
        $score = get_post_meta( $item->ID, '_cbt_result_score', true );
        $total = get_post_meta( $item->ID, '_cbt_result_total_objective', true ); // This needs to be improved for theory questions
        return $score . ' / ' . $total;
    }

    public function column_status( $item ) {
        return ucfirst( get_post_meta( $item->ID, '_cbt_grading_status', true ) );
    }
}
