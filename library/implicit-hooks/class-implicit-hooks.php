<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_defined( 'ImplicitHooks' ) ) {
	
	class ImplicitHooksClassLoader {
		
		protected $services;
		protected $hook_dir;
		protected $prefix;
		protected $suffix;
		
		protected $instance = array();
		
		function  __construct( $base_dir, $config_dir, $hook_dir, $plugin_class_prefix, $hooks_files_prefix, $hooks_files_suffix ) {
			
			$base_dir   = rtrim( $base_dir, '/' );
			$config_dir = $base_dir .'/'. $config_dir;
			$hook_dir   = $base_dir .'/'. $dir;
			
			$this->services = new ImplicitHooksServices( $base_dir, $config_dir, $plugin_class_prefix ); // conditional on init
			$this->hook_dir = $hook_dir;
			$this->prefix   = $prefix;
			$this->suffix   = $suffix;
			$this->services = $services;
		}
		
		public function instance( $instance ) {
			if( isset( $this->instance[ $instance ] ) ) {
				return $this->instance[ $instance ];
			} else {
				false;
			}
		}
		
		public function load() {
			$dir = rtrim( $this->dir, '/' );
			$hooks_files = glob( "{$this->dir}/{$this->prefix}*{$this->suffix}" );
			foreach ( $hooks_files as $hook_file ) {
				$class_name = $plugin_class_prefix . str_replace(' ', '', 
					ucwords(
						str_replace('-', ' ',
							preg_replace('`^' . preg_quote($prefix).'(.*)' . preg_quote($prefix) .'$`', '$1', $hook_file )
						)
					) 
				);
				
				require_once( $hook_file );
				
				if( class_exists( $class_name ) ) {
					$this->instance[ $class_name ] = new $class_name( $this->services );
				}
				
			}
		}
	}
	
	class ImplicitHooksServices {
		
		protected $sevices  = null;
		protected $base_dir = null;
		
		function  __construct( $base_dir, $config_dir, $plugin_class_prefix ) {
			
			$this->base_dir = rtrim( $base_dir, '/' );
			$config_dir = rtrim( $config_dir, '/' );
			
			// services file
			require_once $config_dir. '/services.php';
			
			$sevices = apply_filters( 'implicit_hooks_services', $sevices, $plugin_class_prefix );
			
			// will error if doesn't exist. 
			$this->services =  $services;
			
			// remove variable
			unset( $services );
		}
		
		protected function &arg( $key ) {
			
			if( !preg_match( '`[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff\.]*`', $key ) ) {
				return null;
			}
			
			$parts = explode( '.' , $key );
			
			$key = array_shift( $parts );
			
			if ( isset( $GLOBALS [ $key ] ) ){
				
				if( !empty( $parts ) ) {
					$val_str = '$GLOBALS["'. $key .'"]';
					$val = $GLOBALS [ $key ];
					$break = false;
					
					foreach ( $parts as $part ) {
						
						if ( is_array( $val ) ) {
							
							if ( isset( $val ) ) {
								$val = $val[ $part ];
								$val_str .= '["'. $part .'"]';
							} else {
								$break = true;
							}
							
						} else if ( is_object( $val ) ) {
							
							if ( isset( $val->$part ) ) {
								$val = $val->$part;
								$val_str .= '->' . $part;
							} else {
								$break = true;

							}
						} else {
							$break = true;
						}
						
						if ( $break ) {
							break;
						}
					}
					
					if ( $break ) {
						return null;
					}
					
				} else {
					return $GLOBALS [ $key ];
				}
			}
			
			if ( isset( $val_str ) ) {
				// ensure proper reference
				eval('$val_ref = &' . $val_str . ';');
			} else {
				return null;
			}
			
			return $val_ref;
		}
		
		public function get( $service ) {
			
			if( isset ( $this->sevices[ $service ] )) {
				
				$cur_service = $this->sevices[ $service ];
				
				if ( !isset( $cur_service['instance']) ) {
					require_once( $this->base_dir . '/'. $cur_service['path'] );
					$cur_args = array();
					if ( is_array( $cur_service['args'] ) ) {
						foreach( $cur_service['args'] as $arg_name => $arg ) {
							if( is_string( $arg_name ) ) { // must have label
								if ( is_string( $arg ) && strpos( $arg, '@@' ) === 0 ){ // service prefixed with @@
									$arg_service = ltrim( $arg, '@' );
									$cur_args[ $arg_name ] = $this->get( $arg_service );
								} else if ( is_string( $arg ) && strpos( $arg, '%%' ) === 0 ){ // global prefixed with %%
									$arg_global = ltrim( $arg, '%' );
									$cur_args[ $arg_name ] = &$this->arg( $arg_global ); // pass by reference
								} 
							}
						}
					}
					
					if ( isset( $cur_service['requires'] ) ) {
						foreach ( $cur_service['requires'] as $require ) {
							require_once( $this->base_dir . '/' . $require['path'] );
						}
					}
					
					$service_class = $cur_service['class'];
					$this->sevices[ $service ]['instance'] = new $service_class( $cur_args );
				} 
				
				return $this->sevices[ $service ]['instance'];
			} else {
				return false;
			}
			
		}
		
	}
	
	
	class ImplicitHooks {
		
		protected $services = null;
		
		static $file_loader = null;
		
		function  __construct( $services ) {
			$this->services = $services;
			$this->load_hooks();
			register_activation_hook( __FILE__, array( $this, 'load_activation_hooks' ) );
			register_deactivation_hook( __FILE__, array( $this, 'load_deactivation_hooks' ) );
			$this->action('implicit_hooks_init');
		}
		
		protected function load_hooks( $condition = '' ) {
			$hooks = array();
			$class_methods = get_class_methods();
			$condition = str_replace('__','#', $condition );
			foreach( $class_methods as $method ) {
				
				$is_hook = preg_match( 
					'`^([a-z][a-z0-9_]+)#([a-z][a-z0-9_]+)' . preg_quote( $condition ) . '#(action|filter)(_([0-9]+))?(_([0-9]+))?$`',
					str_replace('__','#', $method ),
					$match
				);
				
				if( $is_hook ){
					
					$callback    = array( $this, $method );
					$hook_name   = $match[2] == 'init' ? 'implicit_hooks_init' : $match[2];
					$hook_type   = "add_{$match[3]}"; // add_action or add_filter
					$priority    = isset($match[5]) ? $match[5] : null;
					$num_args    = isset($match[7]) ? $match[7] : null;
					
					$hook_type( $hook_name, $callback, $priority, $num_args );
					
				}
			}

		}
		
		protected function load_activation_hooks() {
			$this->load_hooks( '__on_activate' );
		}
		
		protected function load_deactivation_hooks() {
			$this->load_hooks( '__on_deactivate' );
		}
		
		protected function event( $name, $type, $args ) { 
			$event_func = "{$type}_ref_array";
			$event_func( $args, $args );
		}
		
		protected function action( $action ) {
			$args = func_get_args();
			array_shift( $args );
			$this->event( $action, 'do_action', $args  );
		}
		
		protected function filter( $filter ) {
			$args = func_get_args();
			array_shift( $args );
			return $this->event( $action, 'apply_filters', $args );
		}

		static public function load( $base_dir, $config_dir,  $hook_dir, $plugin_class_prefix, $hooks_files_prefix = 'class-', $hooks_files_suffix = '-hooks.php' ) {
			self::$file_loader = new ImplicitHooksClassLoader( $base_dir, $config_dir, $hook_dir, $plugin_class_prefix, $hooks_files_prefix, $hooks_files_suffix );
			do_action( 'init', array( self::$file_loader, 'load' ));
		}
		
		static function get( $instance ){
			if ( self::$file_loader ) {
				self::$file_loader->instance( $instance );
			} else {
				return false;
			}
		}
		
	}
}
