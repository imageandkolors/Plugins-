<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CBT_Exam_Reports_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Exam', 'cbt-exam-plugin' ),
            'plural'   => __( 'Exams', 'cbt-exam-plugin' ),
            'ajax'     => false
        ] );
    }

    public function get_columns() {
        $columns = [
            'title'         => __( 'Exam Title', 'cbt-exam-plugin' ),
            'submissions'   => __( 'Submissions', 'cbt-exam-plugin' ),
            'average_score' => __( 'Average Score', 'cbt-exam-plugin' ),
        ];
        return $columns;
    }

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $this->items = $this->get_exams_data();
    }

    private function get_exams_data() {
        $exams = get_posts( [
            'post_type' => 'cbt_exam',
            'numberposts' => -1,
        ] );

        $exam_data = [];
        foreach ( $exams as $exam ) {
            $results = get_posts( [
                'post_type' => 'cbt_result',
                'meta_key' => '_cbt_result_exam_id',
                'meta_value' => $exam->ID,
                'numberposts' => -1,
            ] );

            $total_score = 0;
            $submission_count = count( $results );
            foreach ( $results as $result ) {
                $total_score += (int) get_post_meta( $result->ID, '_cbt_result_score', true );
            }
            $average_score = ( $submission_count > 0 ) ? $total_score / $submission_count : 0;

            $exam_data[] = [
                'ID' => $exam->ID,
                'title' => $exam->post_title,
                'submissions' => $submission_count,
                'average_score' => round( $average_score, 2 ),
            ];
        }
        return $exam_data;
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'submissions':
            case 'average_score':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }
    }

    function column_title( $item ) {
        $actions = array(
            'view_report' => sprintf( '<a href="?page=%s&action=%s&exam_id=%s">View Report</a>', $_REQUEST['page'], 'view_exam_report', $item['ID'] ),
        );
        return sprintf('%1$s %2$s', $item['title'], $this->row_actions( $actions ) );
    }
}
