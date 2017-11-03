<?php
class FusionSC_TwoThird {

	public static $args;

	/**
	 * Initiate the shortcode
	 */
	public function __construct() {

		add_filter( 'fusion_attr_two-third-shortcode', array( $this, 'attr' ) );
		add_filter( 'fusion_attr_two-third-shortcode-wrapper', array( $this, 'wrapper_attr' ) );		
		add_shortcode( 'two_third', array( $this, 'render' ) );

	}

	/**
	 * Render the shortcode
	 * 
	 * @param  array $args	 Shortcode paramters
	 * @param  string $content Content between shortcode
	 * @return string		  HTML output
	 */
	function render( $args, $content = '') {

		$defaults =	shortcode_atts(
			array(
				'class'					=> '',
				'id'					=> '',
				'background_color'		=> '',
				'background_image'		=> '',
				'background_position' 	=> 'left top',				
				'background_repeat' 	=> 'no-repeat',				
				'border_color'			=> '',
				'border_size'			=> '',
				'border_style'			=> 'solid',				
				'last'  				=> 'no',
				'padding'				=> '',
				'spacing'				=> 'yes'
			), $args
		);

		extract( $defaults );

		self::$args = $defaults;
		
		$clearfix = '';
		if( self::$args['last'] == 'yes' ) {
			$clearfix = sprintf( '<div %s></div>', FusionCore_Plugin::attributes( 'fusion-clearfix' ) );
		}	

		$html = sprintf( '<div %s><div %s>%s</div></div>%s', FusionCore_Plugin::attributes( 'two-third-shortcode' ), FusionCore_Plugin::attributes( 'two-third-shortcode-wrapper' ), do_shortcode( $content ), $clearfix );

		return $html;

	}

	function attr() {

		$attr['class'] = 'fusion-two-third two_third fusion-layout-column fusion-column';
		
		if( self::$args['spacing'] == 'no' ) {
			$attr['style'] = 'width:66.6666%';
		}	

		if( self::$args['last'] == 'yes' || 
			self::$args['spacing'] == 'no'
		) {
			$attr['class'] .= ' last';
		}

		$attr['class'] .= ' spacing-' . self::$args['spacing'];

		if( self::$args['class'] ) {
			$attr['class'] .= ' ' . self::$args['class'];
		}

		if( self::$args['id'] ) {
			$attr['id'] = self::$args['id'];
		}

		return $attr;

	}
	
	function wrapper_attr() {
		$attr = array();
	
		$attr['class'] = 'fusion-column-wrapper';

		$attr['style'] = '';


		if( self::$args['background_image'] ) {
			$attr['style'] .= sprintf( 'background:url(%s) %s %s %s;', self::$args['background_image'], self::$args['background_position'], self::$args['background_repeat'], self::$args['background_color']  );
			
			if( self::$args['background_repeat'] == 'no-repeat') {
				$attr['style'] .= '-webkit-background-size:cover;-moz-background-size:cover;-o-background-size:cover;background-size:cover;';
			}			
		} elseif( self::$args['background_color'] ) {
			$attr['style'] .= sprintf( 'background-color:%s;', self::$args['background_color'] );
		}	
		
		if( self::$args['border_color'] && self::$args['border_size'] && self::$args['border_style'] ) {
			if( FusionCore_Plugin::is_transparent_color( self::$args['border_color'] ) ) {
				$attr['style'] .= sprintf( 'outline:%s %s %s;', self::$args['border_size'], self::$args['border_style'], self::$args['border_color'] );
				$attr['style'] .= sprintf( 'outline-offset: -%s;', self::$args['border_size'] );
			} else {
				$attr['style'] .= sprintf( 'border:%s %s %s;', self::$args['border_size'], self::$args['border_style'], self::$args['border_color'] );
			}
		}	
	
		if( self::$args['padding'] ) {
			$attr['style'] .= sprintf( 'padding:%s;', self::$args['padding'] );
		}
			
		return $attr;
	}	

}

new FusionSC_TwoThird();