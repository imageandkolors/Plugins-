<?php
/**
 * Elementor Result Table Widget.
 *
 * Elementor widget that displays a table of the current user's exam results.
 *
 * @since 1.3.0
 */
class Elementor_Result_Table_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'result-table';
    }

    public function get_title() {
        return esc_html__( 'Result Table', 'cbt-exam-plugin' );
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return [ 'cbt-exam' ];
    }

    protected function render() {
        if ( ! is_user_logged_in() ) {
            echo '<p>' . esc_html__( 'You must be logged in to view your results.', 'cbt-exam-plugin' ) . '</p>';
            return;
        }

        $user_id = get_current_user_id();

        $results_query = new \WP_Query( [
            'post_type' => 'cbt_result',
            'author'    => $user_id,
            'posts_per_page' => -1,
        ] );

        if ( ! $results_query->have_posts() ) {
            echo '<p>' . esc_html__( 'You have not completed any exams yet.', 'cbt-exam-plugin' ) . '</p>';
            return;
        }
        ?>
        <table class="cbt-result-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Exam', 'cbt-exam-plugin' ); ?></th>
                    <th><?php esc_html_e( 'Score', 'cbt-exam-plugin' ); ?></th>
                    <th><?php esc_html_e( 'Percentage', 'cbt-exam-plugin' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'cbt-exam-plugin' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'cbt-exam-plugin' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ( $results_query->have_posts() ) {
                    $results_query->the_post();
                    $result_id = get_the_ID();
                    $exam_id = get_post_meta( $result_id, '_cbt_result_exam_id', true );
                    $score = get_post_meta( $result_id, '_cbt_result_score', true );
                    $total = get_post_meta( $result_id, '_cbt_result_total_objective', true ); // Assuming total is based on objective for now
                    $percentage = get_post_meta( $result_id, '_cbt_result_percentage', true );
                    $status = get_post_meta( $result_id, '_cbt_grading_status', true );
                    ?>
                    <tr>
                        <td><?php echo get_the_title( $exam_id ); ?></td>
                        <td><?php echo esc_html( $score ); ?></td>
                        <td><?php echo esc_html( $percentage ); ?>%</td>
                        <td><?php echo esc_html( ucfirst( $status ) ); ?></td>
                        <td><?php echo get_the_date( '', $result_id ); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <?php
        wp_reset_postdata();
    }

}
