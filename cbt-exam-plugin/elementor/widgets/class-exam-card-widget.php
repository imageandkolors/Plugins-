<?php
/**
 * Elementor Exam Card Widget.
 *
 * Elementor widget that displays a card with exam information.
 *
 * @since 1.1.0
 */
class Elementor_Exam_Card_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve exam card widget name.
     *
     * @since 1.1.0
     * @access public
     * @return string Widget name.
     */
    public function get_name() {
        return 'exam-card';
    }

    /**
     * Get widget title.
     *
     * Retrieve exam card widget title.
     *
     * @since 1.1.0
     * @access public
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__( 'Exam Card', 'cbt-exam-plugin' );
    }

    /**
     * Get widget icon.
     *
     * Retrieve exam card widget icon.
     *
     * @since 1.1.0
     * @access public
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-call-to-action';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the exam card widget belongs to.
     *
     * @since 1.1.0
     * @access public
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'cbt-exam' ];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the exam card widget belongs to.
     *
     * @since 1.1.0
     * @access public
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return [ 'exam', 'cbt', 'card', 'test' ];
    }

    /**
     * Register exam card widget controls.
     *
     * Add input fields to allow the user to customize the widget settings.
     *
     * @since 1.1.0
     * @access protected
     */
    protected function register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Content', 'cbt-exam-plugin' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'exam_id',
            [
                'label' => esc_html__( 'Select Exam', 'cbt-exam-plugin' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_available_exams(),
                'default' => '',
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Render exam card widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.1.0
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();
        $exam_id = $settings['exam_id'];

        if ( empty( $exam_id ) ) {
            return;
        }

        $exam_post = get_post( $exam_id );
        if ( ! $exam_post ) {
            return;
        }

        $duration = get_post_meta( $exam_id, '_cbt_exam_duration', true );
        $question_count = count( get_post_meta( $exam_id, '_cbt_exam_questions', true ) );
        ?>
        <div class="elementor-exam-card">
            <h3><?php echo esc_html( $exam_post->post_title ); ?></h3>
            <p><?php echo esc_html( $exam_post->post_excerpt ); ?></p>
            <ul>
                <li><strong><?php esc_html_e( 'Duration:', 'cbt-exam-plugin' ); ?></strong> <?php echo esc_html( $duration ); ?> <?php esc_html_e( 'minutes', 'cbt-exam-plugin' ); ?></li>
                <li><strong><?php esc_html_e( 'Questions:', 'cbt-exam-plugin' ); ?></strong> <?php echo esc_html( $question_count ); ?></li>
            </ul>
            <a href="<?php echo get_permalink( $exam_post ); ?>" class="elementor-button">
                <?php esc_html_e( 'Start Exam', 'cbt-exam-plugin' ); ?>
            </a>
        </div>
        <?php

    }

    /**
     * Get available exams for the select control.
     *
     * @since 1.1.0
     * @access private
     * @return array
     */
    private function get_available_exams() {
        $exams = get_posts( [
            'post_type' => 'cbt_exam',
            'numberposts' => -1,
        ] );

        $options = [];
        if ( ! empty( $exams ) ) {
            foreach ( $exams as $exam ) {
                $options[ $exam->ID ] = $exam->post_title;
            }
        }
        return $options;
    }

}
