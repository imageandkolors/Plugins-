<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class CBT_Grading_List_Table extends WP_List_Table {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Result', 'cbt-exam-plugin' ),
            'plural'   => __( 'Results', 'cbt-exam-plugin' ),
            'ajax'     => false
        ] );
    }

    /**
     * Get the list of columns.
     *
     * @return array
     */
    public function get_columns() {
        $columns = [
            'cb'          => '<input type="checkbox" />',
            'exam_title'  => __( 'Exam', 'cbt-exam-plugin' ),
            'student'     => __( 'Student', 'cbt-exam-plugin' ),
            'date'        => __( 'Date', 'cbt-exam-plugin' ),
        ];
        return $columns;
    }

    /**
     * Prepare the items for the table to process.
     */
    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array( $columns, $hidden, $sortable );

        $this->items = $this->get_pending_results();
    }

    /**
     * Get the pending results from the database.
     *
     * @return array
     */
    private function get_pending_results() {
        $args = [
            'post_type'  => 'cbt_result',
            'meta_key'   => '_cbt_grading_status',
            'meta_value' => 'pending',
        ];
        $query = new WP_Query( $args );
        $results = [];
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $results[] = get_post();
            }
        }
        wp_reset_postdata();
        return $results;
    }

    /**
     * Get the hidden columns.
     *
     * @return array
     */
    public function get_hidden_columns() {
        return array();
    }

    /**
     * Get the sortable columns.
     *
     * @return array
     */
    public function get_sortable_columns() {
        return array( 'date' => array( 'date', false ) );
    }

    /**
     * Render the default column.
     *
     * @param object $item
     * @param string $column_name
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'date':
                return get_the_date( '', $item );
            default:
                return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the checkbox column.
     *
     * @param object $item
     * @return string
     */
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item->ID
        );
    }

    /**
     * Render the exam title column.
     *
     * @param object $item
     * @return string
     */
    public function column_exam_title( $item ) {
        $exam_id = get_post_meta( $item->ID, '_cbt_result_exam_id', true );
        $title = get_the_title( $exam_id );
        $actions = array(
            'grade' => sprintf( '<a href="?page=%s&action=%s&result_id=%s">Grade</a>', $_REQUEST['page'], 'grade', $item->ID ),
        );
        return $title . $this->row_actions( $actions );
    }

    /**
     * Render the student column.
     *
     * @param object $item
     * @return string
     */
    public function column_student( $item ) {
        $user = get_userdata( $item->post_author );
        return $user->display_name;
    }
}
