<?php
/**
 * Front-end QCI student list shortcode.
 *
 * @package Thompson_Engineering_QCI_Database
 */

class TEQCIDB_Shortcode_QCI_List {

    const SHORTCODE_TAG = 'teqcidb_qci_list_shortcode';

    /**
     * Register shortcode and assets.
     */
    public function register() {
        add_shortcode( self::SHORTCODE_TAG, array( $this, 'render' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Render shortcode output.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public function render( $atts ) {
        $atts = shortcode_atts(
            array(
                'per_page' => 25,
            ),
            (array) $atts,
            self::SHORTCODE_TAG
        );

        $per_page = max( 1, absint( $atts['per_page'] ) );
        $per_page = min( $per_page, 100 );

        $filters = array(
            'first_name' => $this->get_query_filter_value( 'teqcidb_qci_first_name' ),
            'last_name'  => $this->get_query_filter_value( 'teqcidb_qci_last_name' ),
            'company'    => $this->get_query_filter_value( 'teqcidb_qci_company' ),
            'city'       => $this->get_query_filter_value( 'teqcidb_qci_city' ),
            'state'      => strtoupper( $this->get_query_filter_value( 'teqcidb_qci_state' ) ),
            'qcinumber'  => $this->get_query_filter_value( 'teqcidb_qci_number' ),
        );

        $page = isset( $_GET['teqcidb_qci_page'] ) ? absint( wp_unslash( $_GET['teqcidb_qci_page'] ) ) : 1;

        if ( $page <= 0 ) {
            $page = 1;
        }

        $results      = $this->get_qci_students( $filters, $page, $per_page );
        $students     = isset( $results['students'] ) && is_array( $results['students'] ) ? $results['students'] : array();
        $total        = isset( $results['total'] ) ? absint( $results['total'] ) : 0;
        $total_pages  = $per_page > 0 ? (int) ceil( $total / $per_page ) : 1;
        $current_page = max( 1, min( $page, max( 1, $total_pages ) ) );

        ob_start();
        ?>
        <section class="teqcidb-qci-list" aria-label="<?php echo esc_attr__( 'QCI Student List', 'teqcidb' ); ?>">
            <form method="get" class="teqcidb-qci-list__search" role="search">
                <?php foreach ( $this->get_preserved_query_vars() as $key => $value ) : ?>
                    <input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" />
                <?php endforeach; ?>

                <div class="teqcidb-qci-list__search-grid">
                    <label class="teqcidb-qci-list__field">
                        <span><?php echo esc_html__( 'First Name', 'teqcidb' ); ?></span>
                        <input type="text" name="teqcidb_qci_first_name" value="<?php echo esc_attr( $filters['first_name'] ); ?>" />
                    </label>
                    <label class="teqcidb-qci-list__field">
                        <span><?php echo esc_html__( 'Last Name', 'teqcidb' ); ?></span>
                        <input type="text" name="teqcidb_qci_last_name" value="<?php echo esc_attr( $filters['last_name'] ); ?>" />
                    </label>
                    <label class="teqcidb-qci-list__field">
                        <span><?php echo esc_html__( 'Company', 'teqcidb' ); ?></span>
                        <input type="text" name="teqcidb_qci_company" value="<?php echo esc_attr( $filters['company'] ); ?>" />
                    </label>
                    <label class="teqcidb-qci-list__field">
                        <span><?php echo esc_html__( 'City', 'teqcidb' ); ?></span>
                        <input type="text" name="teqcidb_qci_city" value="<?php echo esc_attr( $filters['city'] ); ?>" />
                    </label>
                    <label class="teqcidb-qci-list__field">
                        <span><?php echo esc_html__( 'State', 'teqcidb' ); ?></span>
                        <input type="text" maxlength="2" name="teqcidb_qci_state" value="<?php echo esc_attr( $filters['state'] ); ?>" />
                    </label>
                    <label class="teqcidb-qci-list__field">
                        <span><?php echo esc_html__( 'QCI Number', 'teqcidb' ); ?></span>
                        <input type="text" name="teqcidb_qci_number" value="<?php echo esc_attr( $filters['qcinumber'] ); ?>" />
                    </label>
                </div>

                <div class="teqcidb-qci-list__search-actions">
                    <button type="submit" class="button button-primary"><?php echo esc_html__( 'Search', 'teqcidb' ); ?></button>
                    <a class="button button-secondary" href="<?php echo esc_url( $this->get_clear_search_url() ); ?>"><?php echo esc_html__( 'Reset Search Fields', 'teqcidb' ); ?></a>
                </div>
            </form>

            <p class="teqcidb-qci-list__count">
                <?php
                printf(
                    /* translators: %d: total matching students. */
                    esc_html__( 'Showing %d QCI students.', 'teqcidb' ),
                    $total
                );
                ?>
            </p>

            <?php if ( ! empty( $students ) ) : ?>
                <div class="teqcidb-qci-list__accordion" data-teqcidb-qci-list>
                    <?php foreach ( $students as $student ) : ?>
                        <details class="teqcidb-qci-list__item">
                            <summary class="teqcidb-qci-list__summary">
                                <?php echo esc_html( $student['last_name'] . ', ' . $student['first_name'] ); ?>
                            </summary>
                            <div class="teqcidb-qci-list__details">
                                <div><strong><?php echo esc_html__( 'First Name', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['first_name'] ); ?></div>
                                <div><strong><?php echo esc_html__( 'Last Name', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['last_name'] ); ?></div>
                                <div><strong><?php echo esc_html__( 'Company', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['company'] ); ?></div>
                                <div><strong><?php echo esc_html__( 'City', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['city'] ); ?></div>
                                <div><strong><?php echo esc_html__( 'State', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['state'] ); ?></div>
                                <div><strong><?php echo esc_html__( 'QCI Number', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['qcinumber'] ); ?></div>
                                <div><strong><?php echo esc_html__( 'Expiration Date', 'teqcidb' ); ?>:</strong> <?php echo esc_html( $student['expiration_date'] ); ?></div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>

                <?php echo wp_kses_post( $this->render_pagination( $current_page, $total_pages ) ); ?>
            <?php else : ?>
                <p class="teqcidb-dashboard-empty"><?php echo esc_html__( 'No QCI students matched your search.', 'teqcidb' ); ?></p>
            <?php endif; ?>
        </section>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * Enqueue frontend assets for shortcode pages.
     */
    public function enqueue_assets() {
        if ( is_admin() || ! is_singular() ) {
            return;
        }

        global $post;

        if ( ! ( $post instanceof WP_Post ) ) {
            return;
        }

        if ( has_shortcode( (string) $post->post_content, self::SHORTCODE_TAG ) ) {
            wp_enqueue_style(
                'teqcidb-shortcode-qci-list',
                TEQCIDB_PLUGIN_URL . 'assets/css/shortcodes/qci-list.css',
                array(),
                TEQCIDB_VERSION
            );
        }
    }

    /**
     * Query students with QCI numbers.
     *
     * @param array  $filters  Search filters.
     * @param int    $page     Current page.
     * @param int    $per_page Rows per page.
     *
     * @return array{students: array<int,array<string,string>>, total:int}
     */
    private function get_qci_students( array $filters, $page, $per_page ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'teqcidb_students';
        $like       = $wpdb->esc_like( $table_name );
        $found      = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $like ) );

        if ( $found !== $table_name ) {
            return array(
                'students' => array(),
                'total'    => 0,
            );
        }

        $where_clauses = array(
            "qcinumber IS NOT NULL",
            "qcinumber <> ''",
        );
        $params        = array();

        $filter_map = array(
            'first_name' => 'first_name',
            'last_name'  => 'last_name',
            'company'    => 'company',
            'qcinumber'  => 'qcinumber',
        );

        foreach ( $filter_map as $filter_key => $column ) {
            if ( '' === $filters[ $filter_key ] ) {
                continue;
            }

            $where_clauses[] = "$column LIKE %s";
            $params[]        = '%' . $wpdb->esc_like( $filters[ $filter_key ] ) . '%';
        }

        if ( '' !== $filters['city'] ) {
            $where_clauses[] = 'student_address LIKE %s';
            $params[]        = '%' . $wpdb->esc_like( '"city":"' . $filters['city'] ) . '%';
        }

        if ( '' !== $filters['state'] ) {
            $where_clauses[] = 'student_address LIKE %s';
            $params[]        = '%' . $wpdb->esc_like( '"state":"' . strtoupper( $filters['state'] ) ) . '%';
        }

        $where_sql   = implode( ' AND ', $where_clauses );
        $count_query = "SELECT COUNT(*) FROM $table_name WHERE $where_sql";
        $total       = (int) (
            empty( $params )
                ? $wpdb->get_var( $count_query )
                : $wpdb->get_var( $wpdb->prepare( $count_query, $params ) )
        );

        if ( $total <= 0 ) {
            return array(
                'students' => array(),
                'total'    => 0,
            );
        }

        $page   = max( 1, absint( $page ) );
        $offset = ( $page - 1 ) * $per_page;

        if ( $offset < 0 ) {
            $offset = 0;
        }

        $query_params = array_merge( $params, array( $per_page, $offset ) );
        $query        = "SELECT first_name, last_name, company, student_address, qcinumber, expiration_date
            FROM $table_name
            WHERE $where_sql
            ORDER BY last_name ASC, first_name ASC, id ASC
            LIMIT %d OFFSET %d";
        $rows         = $wpdb->get_results( $wpdb->prepare( $query, $query_params ), ARRAY_A );

        if ( ! is_array( $rows ) ) {
            $rows = array();
        }

        $students = array();

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $address = $this->decode_student_address( isset( $row['student_address'] ) ? (string) $row['student_address'] : '' );

            $students[] = array(
                'first_name'      => sanitize_text_field( (string) ( isset( $row['first_name'] ) ? $row['first_name'] : '' ) ),
                'last_name'       => sanitize_text_field( (string) ( isset( $row['last_name'] ) ? $row['last_name'] : '' ) ),
                'company'         => sanitize_text_field( (string) ( isset( $row['company'] ) ? $row['company'] : '' ) ),
                'city'            => sanitize_text_field( (string) $address['city'] ),
                'state'           => sanitize_text_field( (string) $address['state'] ),
                'qcinumber'       => sanitize_text_field( (string) ( isset( $row['qcinumber'] ) ? $row['qcinumber'] : '' ) ),
                'expiration_date' => $this->format_date_for_display( isset( $row['expiration_date'] ) ? $row['expiration_date'] : '' ),
            );
        }

        return array(
            'students' => $students,
            'total'    => $total,
        );
    }

    /**
     * Decode stored student address JSON.
     *
     * @param string $address_json Address JSON.
     *
     * @return array{city:string,state:string}
     */
    private function decode_student_address( $address_json ) {
        $defaults = array(
            'city'  => '',
            'state' => '',
        );

        $decoded = json_decode( (string) $address_json, true );

        if ( ! is_array( $decoded ) ) {
            return $defaults;
        }

        return array(
            'city'  => isset( $decoded['city'] ) ? (string) $decoded['city'] : '',
            'state' => isset( $decoded['state'] ) ? (string) $decoded['state'] : '',
        );
    }

    /**
     * Render pagination controls.
     *
     * @param int $current_page Current page number.
     * @param int $total_pages  Total pages.
     *
     * @return string
     */
    private function render_pagination( $current_page, $total_pages ) {
        if ( $total_pages <= 1 ) {
            return '';
        }

        $base_args = $this->get_current_shortcode_query_args();
        unset( $base_args['teqcidb_qci_page'] );

        $markup = '<nav class="teqcidb-qci-list__pagination" aria-label="' . esc_attr__( 'QCI list pagination', 'teqcidb' ) . '">';

        for ( $page = 1; $page <= $total_pages; $page++ ) {
            $url      = add_query_arg( array_merge( $base_args, array( 'teqcidb_qci_page' => $page ) ) );
            $is_active = $page === (int) $current_page;

            if ( $is_active ) {
                $markup .= '<span class="teqcidb-qci-list__page is-active" aria-current="page">' . esc_html( (string) $page ) . '</span>';
            } else {
                $markup .= '<a class="teqcidb-qci-list__page" href="' . esc_url( $url ) . '">' . esc_html( (string) $page ) . '</a>';
            }
        }

        $markup .= '</nav>';

        return $markup;
    }

    /**
     * Build clear-search URL.
     *
     * @return string
     */
    private function get_clear_search_url() {
        $args = $this->get_current_shortcode_query_args();

        foreach ( array(
            'teqcidb_qci_first_name',
            'teqcidb_qci_last_name',
            'teqcidb_qci_company',
            'teqcidb_qci_city',
            'teqcidb_qci_state',
            'teqcidb_qci_number',
            'teqcidb_qci_page',
        ) as $remove_key ) {
            unset( $args[ $remove_key ] );
        }

        return add_query_arg( $args );
    }

    /**
     * Return current query args relevant to this request.
     *
     * @return array<string,string>
     */
    private function get_current_shortcode_query_args() {
        $args = array();

        foreach ( (array) $_GET as $key => $value ) {
            if ( ! is_scalar( $value ) ) {
                continue;
            }

            $args[ sanitize_key( (string) $key ) ] = sanitize_text_field( wp_unslash( (string) $value ) );
        }

        return $args;
    }

    /**
     * Return hidden vars to preserve request context.
     *
     * @return array<string,string>
     */
    private function get_preserved_query_vars() {
        $args = $this->get_current_shortcode_query_args();

        foreach ( array(
            'teqcidb_qci_first_name',
            'teqcidb_qci_last_name',
            'teqcidb_qci_company',
            'teqcidb_qci_city',
            'teqcidb_qci_state',
            'teqcidb_qci_number',
            'teqcidb_qci_page',
        ) as $remove_key ) {
            unset( $args[ $remove_key ] );
        }

        return $args;
    }

    /**
     * Get and sanitize a shortcode filter from query args.
     *
     * @param string $key Query arg key.
     *
     * @return string
     */
    private function get_query_filter_value( $key ) {
        if ( ! isset( $_GET[ $key ] ) ) {
            return '';
        }

        return sanitize_text_field( (string) wp_unslash( $_GET[ $key ] ) );
    }

    /**
     * Format a DB date for front-end display.
     *
     * @param string $value Raw date.
     *
     * @return string
     */
    private function format_date_for_display( $value ) {
        $value = sanitize_text_field( (string) $value );

        if ( '' === $value || '0000-00-00' === $value ) {
            return '';
        }

        $date = date_create( $value );

        if ( ! $date ) {
            return '';
        }

        return $date->format( 'm-d-Y' );
    }
}
