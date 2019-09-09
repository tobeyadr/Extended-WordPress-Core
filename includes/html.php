<?php
namespace ExtendedCore;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HTML
 *
 * Helper class for reusable html markup. Mostly input steps and form steps.
 *
 * @package     Includes
 * @author      Adrian Tobey <info@groundhogg.io>
 * @copyright   Copyright (c) 2018, Groundhogg Inc.
 * @license     https://opensource.org/licenses/GPL-3.0 GNU Public License v3
 * @since       File available since Release 0.1
 */
abstract class HTML
{

    /**
     * Turn the GET into inputs for a nav form
     *
     * @param bool $echo
     * @return string
     */
    public function hidden_GET_inputs( $echo = true )
    {
        $html = '';

        foreach ( $_GET as $key => $value ) {
            $html .= $this->input( [ 'type' => 'hidden', 'name' => $key, 'value' => $value ] );
        }

        if ( $echo ){
            echo $html;
        }

        return $html;

    }

    /**
     * @param array $args
     * @param array $cols
     * @param array $rows
     * @param bool $footer
     */
    public function list_table($args=[], $cols=[], $rows=[], $footer=true )
    {
        $args = wp_parse_args( $args, [
            'class' => ''
        ] );

        $args[ 'class' ] .= 'wp-list-table widefat fixed striped';

        ?>
        <table <?php echo array_to_atts( $args ); ?> >
        <thead>
        <tr>
            <?php foreach ( $cols as $col => $name ): ?>
            <th><?php echo $name; ?></th>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <?php if ( ! empty( $rows ) ): ?>

        <?php foreach ( $rows as $row => $cells ): ?>
            <tr>
                <?php foreach ( $cells as $cell => $content ): ?>
                    <td><?php echo $content; ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        <?php else:

        $col_span = count( $cols );
        echo $this->wrap( __( 'No items found.', 'groundhogg' ), 'td', [ 'colspan' => $col_span ] );

        endif; ?>
        </tbody>
            <?php if ( $footer ): ?>
        <tfoot>
        <?php foreach ( $cols as $col => $name ): ?>
            <th><?php echo $name; ?></th>
        <?php endforeach; ?>
        </tfoot>
        <?php endif; ?>
        </table>
        <?php
    }

    public function tabs( $tabs=[], $active_tab=false )
    {
        if ( empty( $tabs ) ){
            return;
        }

        if ( ! $active_tab ){
            $active_tab = get_request_var( 'tab' );

            // Get first Tab
            if ( ! $active_tab ){
                $tab_keys = array_keys( $tabs );
                $active_tab = array_shift( $tab_keys );
            }
        }

        ?>
        <h2 class="nav-tab-wrapper">
            <?php foreach ( $tabs as $id => $tab ):

                echo $this->e( 'a', [
                    'href' => esc_url( add_query_arg( [ 'tab' => $id ], $_SERVER[ 'REQUEST_URI' ] ) ),
                    'class' => 'nav-tab' . ( $active_tab == $id ? ' nav-tab-active' : '' ),
                    'id' => $id,
                ], $tab );

            endforeach; ?>
        </h2>
        <?php
    }

    /**
     * Start a form table cuz we use LOTS of those!!!
     *
     * @param array $args
     */
    public function start_form_table( $args=[] )
    {
        $args = wp_parse_args( $args, [
            'title' => '',
            'class' => ''
        ] );

        if ( ! empty( $args[ 'title' ] ) ){
            ?><h3><?php echo $args[ 'title' ]; ?></h3><?php
        }
        ?>
<table class="form-table <?php esc_attr_e( $args[ 'class' ] ) ?>">
    <tbody>
<?php
    }

    public function start_row( $args = [] )
    {
        $args = wp_parse_args( $args, [
            'title' => '',
            'class' => '',
            'id' => ''
        ] );

        printf( "<tr title='%s' class='%s' id='%s'>",
            esc_attr( $args[ 'title' ] ),
            esc_attr( $args[ 'class' ] ),
            esc_attr( $args[ 'id' ] ) );
    }

    public function end_row( $args = [] )
    {
        printf( "</tr>" );
    }

    public function th( $content, $args = [] )
    {
        if ( is_array( $content ) ){
            $content = implode( '', $content );
        }

        $args = wp_parse_args( $args, [
            'title' => '',
            'class' => '',
        ] );

        echo $this->wrap( $content, 'th', $args );
    }

    public function td( $content, $args = [] )
    {
        if ( is_array( $content ) ){
            $content = implode( '', $content );
        }

        $args = wp_parse_args( $args, [
            'title' => '',
            'class' => '',
        ] );

        echo $this->wrap( $content, 'td', $args );
    }

    /**
     * Return P description.
     *
     * @param $text
     * @return string
     */
    public function description( $text ){
        return sprintf( '<p class="description">%s</p>', $text );
    }

    /**
     * Wrap arbitraty HTML in another element
     *
     * @param string $content
     * @param string $e
     * @param array $atts
     * @return string
     */
    public function wrap( $content = '', $e = 'div', $atts = [] )
    {
        if ( is_array( $content ) ){
            $content = implode( '', $content );
        }

        return sprintf( '<%1$s %2$s>%3$s</%1$s>', esc_html( $e ), array_to_atts( $atts ), $content );
    }

	/**
     * Generate an html element.
     *
	 * @param string $e
	 * @param array $atts
	 * @param bool $self_closing
	 *
	 * @return string
	 */
    public function e( $e = 'div', $atts = [], $content='', $self_closing = true )
    {
        if ( ! empty( $content ) || ! $self_closing ){
            return $this->wrap( $content, $e, $atts );
        }

        return sprintf( '<%1$s %2$s/>', esc_html( $e ), array_to_atts( $atts ) );
    }


    public function end_form_table()
    {
        ?></tbody></table><?php
    }

	/**
	 * Output a simple input field
	 *
	 * @param $args
	 * @return string
	 */
	public function input( $args=[] )
	{
		$a = wp_parse_args( $args, array(
			'type'  => 'text',
			'name'  => '',
			'id'    => '',
			'class' => 'regular-text',
			'value' => '',
		) );

		$html = $this->e( 'input', $a );

		return apply_filters( 'extended_core/html/input', $html, $a );
	}

	/**
	 * Wrapper function for the INPUT
	 *
	 * @param $args
	 * @return string
	 */
	public function number( $args=[] )
	{

		$a = wp_parse_args( $args, array(
			'type'  => 'number',
			'name'  => '',
			'id'    => '',
			'class' => 'regular-text',
			'value' => '',
			'attributes' => '',
			'placeholder' => '',
			'min'       => 0,
			'max'       => 99999,
			'step'      => 1
		) );

		return $this->input( $a );
	}

	/**
	 * Output a button
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function button( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'type'      => 'button',
            'text'      => '',
            'name'      => '',
            'id'        => '',
            'class'     => 'button button-secondary',
            'value'     => '',
        ) );

        $text = $a[ 'text' ];
        unset( $a[ 'text' ] );

        return $this->wrap( $text, 'button', $a );
    }

	/**
	 * Output a checkbox
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function checkbox( $args=[] )
	{
		$a = shortcode_atts( array(
			'label'         => '',
			'type'          => 'checkbox',
			'name'          => '',
			'id'            => '',
			'class'         => '',
			'value'         => '1',
			'checked'       => false,
			'title'         => '',
		), $args );

		return $this->wrap( $this->input( $a ) . '&nbsp;' . $a[ 'label' ], 'label', [ 'class' => 'gh-checkbox-label' ] );
	}

    /**
     * Wrapper function for the INPUT
     *
     * @param $args
     * @return string
     */
    public function range( $args=[] )
    {

        $a = wp_parse_args( $args, array(
            'type'  => 'range',
            'name'  => '',
            'id'    => '',
            'class' => 'slider',
            'value' => '',
            'attributes' => '',
            'placeholder' => '',
            'min'       => 0,
            'max'       => 99999,
            'step'      => 1
        ) );

	    return $this->input( $a );
    }

    /**
     * Output a simple textarea field
     *
     * @param $args
     * @return string
     */
    public function textarea( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'name'  => '',
            'id'    => '',
            'class' => '',
            'value' => '',
            'cols'  => '30',
            'rows'  => '7',
            'placeholder'   => '',
        ) );

        $value = $a[ 'value' ];
        unset( $a[ 'value' ] );

        return $this->wrap( esc_html( $value ), 'textarea', $a );
    }

    /**
     * Output simple HTML for a dropdown field.
     *
     * @param $args
     * @return string
     */
    public function dropdown( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'name'              => '',
            'id'                => '',
            'class'             => '',
            'options'           => array(),
            'selected'          => '',
            'multiple'          => false,
            'option_none'       => 'Please Select One',
            'option_none_value' => '',
        ) );

        $a[ 'selected' ] = ensure_array( $a[ 'selected' ] );

        $optionHTML = '';

        if ( ! empty( $a[ 'option_none' ] ) ){
            $optionHTML .= sprintf( "<option value='%s'>%s</option>",
                esc_attr( $a[ 'option_none_value' ] ),
                sanitize_text_field( $a[ 'option_none' ] )
            );
        }

        if ( is_array( get_array_var( $a, 'options' ) ) ) {

            $options = $a[ 'options' ];

            foreach ( $options as $value => $name ){

                /* Include optgroup support */
                if ( is_array( $name ) ){

                    /* Redefine */
                    $inner_options = $name;
                    $label = $value;

                    $optionHTML .= sprintf( "<optgroup label='%s'>", $label );

                    foreach ( $inner_options as $inner_value => $inner_name ){

                        $selected = ( in_array( $inner_value, $a[ 'selected' ] ) ) ? 'selected' : '';

                        $optionHTML .= sprintf(
                            "<option value='%s' %s>%s</option>",
                            esc_attr( $inner_value ),
                            $selected,
                            esc_html( $inner_name )
                        );
                    }

                    $optionHTML .= "</optgroup>";

                } else {
                    $selected = ( in_array( $value, $a[ 'selected' ] ) ) ? 'selected' : '';

                    $optionHTML .= sprintf(
                        "<option value='%s' %s>%s</option>",
                        esc_attr( $value ),
                        $selected,
                        esc_html( $name )
                    );
                }

            }

        }

        if ( ! $a[ 'multiple' ] ){
            unset( $a[ 'multiple' ] );
        }

        unset( $a[ 'option_none' ] );
        unset( $a[ 'attributes' ] );
        unset( $a[ 'option_none_value' ] );
        unset( $a[ 'selected' ] );
        unset( $a[ 'options' ] );

        return $this->wrap( $optionHTML, 'select', $a );
    }

    /**
     * Select 2 html input
     *
     * @param $args
     *
     * @type $selected array list of $value which are selected
     * @type $data array list of $value => $text options for the select 2
     *
     * @return string
     */
    public function select2( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'name'              => '',
            'id'                => '',
            'class'             => '',
            'data'              => [],
            'options'           => [],
            'selected'          => [],
            'multiple'          => false,
            'placeholder'       => 'Please Select One',
            'tags'              => false,
            'style'             => [ 'min-width' => '400px' ]
        ) );

        $a[ 'class' ] .= ' extended-core-select2';

        if ( isset_not_empty( $a, 'data' ) ){
            $a[ 'options' ] = $a[ 'data' ];
        }

        unset( $a[ 'data' ] );

        if ( isset_not_empty( $a, 'placeholder' ) ) {
            $a['data-placeholder'] = $a['placeholder'];
        }

        unset( $a[ 'placeholder' ] );

        if ( isset_not_empty( $a,'tags' ) ){
            $a[ 'data-tags' ] = $a[ 'tags' ];
        }

        unset( $a[ 'tags' ] );

        wp_enqueue_script( 'extended-core-admin' );

        return $this->dropdown( $a );
    }


	/**
     * Output a simple Jquery UI date picker
     *
     * @param $args
     * @return string HTML
     */
    public function date_picker( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'name'  => '',
            'id'    => uniqid( 'date-' ),
            'class' => 'regular-text',
            'value' => '',
            'placeholder' => 'yyyy-mm-dd',
            'min-date' => date( 'Y-m-d', strtotime( 'today' ) ),
            'max-date' => date( 'Y-m-d', strtotime( '+100 years' ) ),
            'format' => 'yy-mm-dd'
        ) );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-ui' );

        $html = $this->input( [
            'type'  => 'text',
            'id'    => $a[ 'id' ],
            'name'  => $a[ 'name' ],
            'class' => $a[ 'class' ],
            'placeholder' => $a[ 'placeholder' ],
            'autocomplete' => $a[ 'autocomplete' ],
        ] );

        $html .= sprintf(
            "<script>jQuery(function($){ $('#%s').datepicker({changeMonth: true,changeYear: true,minDate: '%s', maxDate: '%s',dateFormat:'%s'})});</script>",
            esc_attr( $a[ 'id'      ] ),
            esc_attr( $a[ 'min-date' ] ),
            esc_attr( $a[ 'max-date' ] ),
            esc_attr( $a[ 'format' ] )
        );

        return $html;
    }

    /**
     * Return HTML for a color picker
     *
     * @param $args
     * @return string
     */
    public function color_picker( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'name'      => '',
            'id'        => '',
            'value'     => '',
            'default'   => '',
            'class'     => '',
        ) );

        $a[ 'class' ] .= ' extended-core-color';

        if ( ! isset_not_empty( 'data-default-color' ) ){
            $a[ 'data-default-color' ] = $a[ 'default' ];
            unset( $a[ 'default' ] );
        }

        wp_enqueue_script( 'extended-core-admin' );
	    wp_enqueue_style( 'wp-color-picker' );

	    return $this->input( $a );
    }

    /**
     * Autocomplete link picker
     *
     * @param $args
     * @return string
     */
    public function link_picker( $args=[] )
    {
        $a = wp_parse_args( $args, array(
            'type'  => 'text',
            'name'  => '',
            'id'    => '',
            'class' => 'regular-text',
            'value' => '',
            'placeholder' => __( 'Start typing...', 'extended-core' ),
            'autocomplete' => 'off',
            'required' => false
        ) );

        $a[ 'class' ] .= ' extended-core-link-picker';

        wp_enqueue_script( 'extended-core-admin' );

        return $this->input( $a );
    }
}