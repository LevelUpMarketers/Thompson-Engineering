<?php
/**
 * Register REST routes for class-page quiz runtime.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Rest {

    /**
     * @var TEQCIDB_Ajax
     */
    private $ajax;

    public function __construct( $ajax ) {
        $this->ajax = $ajax;
    }

    public function register() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route(
            'teqcidb/v1',
            '/quiz/progress',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'save_quiz_progress' ),
                'permission_callback' => array( $this, 'can_manage_quiz_request' ),
            )
        );

        register_rest_route(
            'teqcidb/v1',
            '/quiz/submit',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'submit_quiz_attempt' ),
                'permission_callback' => array( $this, 'can_manage_quiz_request' ),
            )
        );
    }

    public function can_manage_quiz_request( $request ) {
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'teqcidb_rest_auth_required', __( 'Please log in to continue.', 'teqcidb' ), array( 'status' => 401 ) );
        }

        $nonce = (string) $request->get_header( 'X-WP-Nonce' );

        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_Error( 'teqcidb_rest_nonce_invalid', __( 'Your session token is invalid. Please refresh and try again.', 'teqcidb' ), array( 'status' => 403 ) );
        }

        return true;
    }

    public function save_quiz_progress( $request ) {
        $validated = $this->validate_request_payload( $request );

        if ( is_wp_error( $validated ) ) {
            return $validated;
        }

        $throttle_error = $this->enforce_progress_rate_limit( get_current_user_id(), $validated['attempt_id'], $validated['quiz_id'], $validated['class_id'] );

        if ( is_wp_error( $throttle_error ) ) {
            return $throttle_error;
        }

        $result = $this->ajax->process_quiz_attempt_request( $validated, get_current_user_id(), false, 'rest' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response(
            array(
                'ok'             => true,
                'attempt_id'     => isset( $result['attempt_id'] ) ? (int) $result['attempt_id'] : 0,
                'saved_at'       => isset( $result['saved_at'] ) ? (string) $result['saved_at'] : current_time( 'mysql' ),
                'server_version' => TEQCIDB_VERSION,
                'message'        => isset( $result['message'] ) ? (string) $result['message'] : __( 'Quiz progress saved.', 'teqcidb' ),
            )
        );
    }

    public function submit_quiz_attempt( $request ) {
        $validated = $this->validate_request_payload( $request );

        if ( is_wp_error( $validated ) ) {
            return $validated;
        }

        $result = $this->ajax->process_quiz_attempt_request( $validated, get_current_user_id(), true, 'rest' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response(
            array(
                'ok'              => true,
                'attempt_id'      => isset( $result['attempt_id'] ) ? (int) $result['attempt_id'] : 0,
                'saved_at'        => isset( $result['saved_at'] ) ? (string) $result['saved_at'] : current_time( 'mysql' ),
                'server_version'  => TEQCIDB_VERSION,
                'message'         => isset( $result['message'] ) ? (string) $result['message'] : __( 'Quiz submitted.', 'teqcidb' ),
                'score'           => isset( $result['score'] ) ? (int) $result['score'] : 0,
                'passThreshold'   => isset( $result['pass_threshold'] ) ? (int) $result['pass_threshold'] : 75,
                'passed'          => ! empty( $result['passed'] ),
                'incorrectDetails'=> isset( $result['incorrect_details'] ) ? $result['incorrect_details'] : array(),
            )
        );
    }

    private function validate_request_payload( $request ) {
        $quiz_id                = absint( $request->get_param( 'quiz_id' ) );
        $class_id               = absint( $request->get_param( 'class_id' ) );
        $attempt_id             = absint( $request->get_param( 'attempt_id' ) );
        $current_question_index = absint( $request->get_param( 'current_question_index' ) );
        $answers_raw            = $request->get_param( 'answers' );

        if ( $quiz_id <= 0 || $class_id <= 0 ) {
            return new WP_Error( 'teqcidb_rest_invalid_ids', __( 'Quiz ID and class ID are required.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        if ( ! is_array( $answers_raw ) ) {
            return new WP_Error( 'teqcidb_rest_invalid_answers', __( 'Answer payload must be an object keyed by question ID.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        $answers = array();

        foreach ( $answers_raw as $question_key => $selected_values ) {
            $question_id = absint( $question_key );

            if ( $question_id <= 0 ) {
                continue;
            }

            if ( ! is_array( $selected_values ) ) {
                $selected_values = array( $selected_values );
            }

            $clean_values = array();

            foreach ( $selected_values as $selected_value ) {
                if ( ! is_scalar( $selected_value ) ) {
                    continue;
                }

                $normalized = sanitize_key( (string) $selected_value );

                if ( '' !== $normalized ) {
                    $clean_values[] = $normalized;
                }
            }

            $answers[ (string) $question_id ] = array_values( array_unique( $clean_values ) );
        }

        return array(
            'quiz_id'       => $quiz_id,
            'class_id'      => $class_id,
            'attempt_id'    => $attempt_id,
            'current_index' => $current_question_index,
            'answers'       => $answers,
        );
    }

    private function enforce_progress_rate_limit( $user_id, $attempt_id, $quiz_id, $class_id ) {
        $user_id = absint( $user_id );

        if ( $user_id <= 0 ) {
            return new WP_Error( 'teqcidb_rest_rate_identity_missing', __( 'Unable to rate-limit this request.', 'teqcidb' ), array( 'status' => 400 ) );
        }

        $attempt_component = $attempt_id > 0 ? $attempt_id : ( $quiz_id . '_' . $class_id );
        $bucket_key        = 'teqcidb_qp_rate_' . md5( $user_id . '_' . $attempt_component );
        $last_request_time = (float) get_transient( $bucket_key );
        $now               = microtime( true );

        if ( $last_request_time > 0 && ( $now - $last_request_time ) < 0.8 ) {
            return new WP_Error(
                'teqcidb_rest_rate_limited',
                __( 'Too many autosave requests. Please retry shortly.', 'teqcidb' ),
                array(
                    'status'      => 429,
                    'retry_after' => 1,
                )
            );
        }

        set_transient( $bucket_key, $now, 10 );

        return true;
    }
}
