<?php

	/**
	 * @package BuddyPress Extend Groups 
	 * @version 1.0
	 */
	/*
	Plugin Name: BP Extend Groups Fields
    Plugin URI: https://github.com/sergordienko/buddypress-extend-fields
	Description: Custom fields for buddypres groups.
	Author: Sergey Gordienko
	Version: 1.0
	Author URI: https://github.com/sergordienko
	*/

if( !class_exists( 'Bp_Extended_Group_Fields' ) ) :

    class Bp_Extended_Group_Fields
    {
        protected $screen_group = FALSE;

        protected $advanced_fields = FALSE;

        public function __construct( )
        {
            $this->register_extend_field( );

            add_action( 'admin_menu', array( $this, 'add_submenu' ) );

            add_action( 'admin_menu', array( $this, 'remove_publish_box' ) );

            add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

            add_action( 'save_post', array( $this, 'post_fields_save' ), 10, 3 );

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

            add_action( 'wp_ajax_group_reorder_fields', array( $this, 'group_reorder_fields' ) );

            //add_action( 'groups_group_details_edited', array( $this, 'group_fields_save' ) );

            $this->advanced_fields( );
        }

        public function advanced_fields( )
        {
            $this->advanced_fields = array(

                'container_id' => __( 'Container ID' ),

                'container_class' => __( 'Container Class' ),

                'field_class' => __( 'Field Class' ),

                'meta_key' => __( 'Meta Key' ),

                'data_validation' => __( 'Data Validation' ),

                'validation_format' => __( 'Validation Format' )
            );
        }

        public function add_submenu( )
        {
            $this->screen_group = add_submenu_page( 'bp-groups', 'Group Fields', 'Group Fields', 'manage_options', 'group-fields', array( $this, 'display_fields' ) );
        }

        public function remove_publish_box( )
        {
            remove_meta_box( 'submitdiv', 'group-fields', 'side' );
        }

        public function add_meta_box( )
        {
            add_meta_box( 'field_group_submit', __( 'Submit' ), array( $this, 'field_group_submit' ),
                 'group-fields', 'side', 'low' );
            add_meta_box( 'field_group_description', empty( $_GET[ 'parent' ] ) ? __( 'Field Group Description' ) : __( 'Field Description' ), array( $this, 'field_group_description' ),
                 'group-fields', 'normal', 'low' );

            if( !empty( $_GET[ 'parent' ] ) )
            {
                add_meta_box( 'field-parent', __( 'Parent' ), array( $this, 'group_field_parent' ), 'group-fields', 'side', 'low' );

                add_meta_box( 'field-type', __( 'Type' ), array( $this, 'type_field' ), 'group-fields', 'normal', 'low' );

                add_meta_box( 'field-advanced-settings', __( 'Advanced Settings' ), array( $this, 'advanced_settings' ), 'group-fields', 'normal', 'low' );

                add_meta_box( 'field-requirement', __( 'Requirement' ), array( $this, 'requirement' ), 'group-fields', 'side', 'low' );

            }
        }

        public function requirement( $post )
        {
            $required = get_post_meta( $post->ID, 'required', true );

            ?>
            <select id="is_required" name="required">
                <option value="0" <?php selected( $required, 0 ); ?>><?php _e( 'Not Required' ); ?></option>
                <option value="1" <?php selected( $required, 1 ); ?>><?php _e( 'Required' ); ?></option>
            </select>
            <?php
        }

        public function type_field( $post )
        {
            $data = get_post_meta( $post->ID, 'fieldtype', true );

            $type = !empty( $data[ 'type' ] ) ? $data[ 'type' ] : 'single';

            $fieldtype = !empty( $data[ 'fieldtype' ] ) ? $data[ 'fieldtype' ] : 'textbox';

            if( empty( $data[ 'options' ] ) ) $data[ 'options' ] = array( 1 => '' );

            include( 'templates/types.php' );
        }

        public function advanced_settings( $post )
        {
            $data = get_post_meta( $post->ID, 'gf_advanced_fields', true );

            foreach( $this->advanced_fields as $name => $label ): ?>
                <p>
                    <label class="widefat" for="<?php echo $name; ?>"><?php echo $label; ?></label><br />
                    <input type="text" id="<?php echo $name; ?>" name="gf_advanced_fields[<?php echo $name; ?>]" value="<?php echo !empty( $data[ $name ] ) ? $data[ $name ] : ''; ?>">
                </p>
            <?php endforeach;
        }

        public function group_field_parent( $post )
        {
            if( !empty( $post->post_parent ) ) $parent = $post->post_parent;

            else if( !empty( $_GET[ 'parent' ] ) ) $parent = $_GET[ 'parent' ];

            $pages = wp_dropdown_pages( array( 'post_type' => 'group-fields', 'selected' => $parent, 'name' => 'parent_id', /*'show_option_none' => __( '(no parent)' ),*/ 'sort_column'=> 'menu_order, post_title', 'depth' => 1, 'echo' => 0 ) );

            if ( !empty( $pages ) ) echo $pages;
        }

        public function field_group_submit( )
        {
            global $action, $post;

            $post_type = $post->post_type;

            $post_type_object = get_post_type_object( $post_type );

            $can_publish = current_user_can( $post_type_object->cap->publish_posts );

            if( !empty( $_GET[ 'parent' ] ) ) $item = 'Field';

            else $item = 'Group Field';

            ?>
            <div class="submitbox" id="submitpost">

                <div id="major-publishing-actions">

                <?php do_action( 'post_submitbox_start' ); ?>

                <div id="delete-action">

                    <?php if( current_user_can( 'delete_post', $post->ID ) ): ?>

                        <?php

                            if( !EMPTY_TRASH_DAYS ) $delete_text = __( 'Delete Permanently' );
                            else $delete_text = __( 'Move to Trash' );
                        ?>

                        <a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>">
                            <?php echo $delete_text; ?>
                        </a>

                    <?php endif; ?>

                </div>

                <div id="publishing-action">

                    <span class="spinner"></span>

                    <?php if( !in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ): ?>
                        <?php if ( $can_publish ) : ?>
                            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Add Tab') ?>" />
                        <?php submit_button( sprintf( __( 'Add %s' ), $item ), 'primary button-large', 'publish', false, array( 'accesskey' => 'p' ) ); ?>
                        <?php endif; ?>
                    <?php else: ?>
                            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update ') . $item; ?>" />
                            <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e('Update ') . $item; ?>" />
                    <?php endif; ?>

                </div>

                <div id="delete-action">
                    <a href="<?php menu_page_url( 'group-fields', true ); ?>" class="deletion"><?php _e( 'Cancel', 'buddypress' ); ?></a>
                </div>

                <div class="clear"></div>

                </div>

            </div>

            <?php
        }

        public function field_group_description(  )
        {
            global $post;

            ?>
            <label for="group_description" class="screen-reader-text">
            <?php
                /* translators: accessibility text */
                esc_html_e( 'Add description', 'buddypress' );
            ?>
            </label>
            <textarea name="group_description" id="group_description" rows="8" class="widefat"><?php echo esc_textarea( get_post_meta( $post->ID, 'group_description', true ) ); ?></textarea>

            <?php
        }

        public function display_fields( )
        {
            if( !empty( $_REQUEST[ 'mode' ] ) )
            {
                if( $_REQUEST[ 'mode' ] == 'delete_group' && !empty( $_REQUEST[ 'group_id' ] ) )
                {
                    $this->delete_group_field( $_REQUEST[ 'group_id' ], true );
                }
                if( $_REQUEST[ 'mode' ] == 'delete_field' && !empty( $_REQUEST[ 'field_id' ] ) )
                {
                    $this->delete_group_field( $_REQUEST[ 'field_id' ], false );
                }
            }

            $user = wp_get_current_user( );

            include( 'templates/form.php' );
        }

        public function delete_group_field( $id, $delete_child )
        {
            $user = wp_get_current_user( );

            if( user_can( $user->ID, 'delete_post' ) )
            {
                $post = get_post( $id );

                if( !empty( $post ) )
                {
                    if( $post->post_type == 'group-fields' )
                    {
                        if( $delete_child )
                        {
                            $childs = get_posts( array( 'post_parent' => $id, 'post_type' => 'group-fields' ) );

                            if( $childs ) foreach( $childs as $child ) wp_delete_post( $child->ID, true );
                        }

                        wp_delete_post( $post->ID, true );
                    }
                }
            }
        }

        public function enqueue_scripts( $hook )
        {
            global $post;

            if( $this->screen_group == $hook || ( !empty( $post->post_type ) && $post->post_type === 'group-fields' ) )
            {
                //wp_enqueue_script( 'jquery-ui-core'      );
                wp_enqueue_script( 'jquery-ui-tabs'      );
                wp_enqueue_script( 'jquery-ui-mouse'     );
                wp_enqueue_script( 'jquery-ui-draggable' );
                wp_enqueue_script( 'jquery-ui-droppable' );
                wp_enqueue_script( 'jquery-ui-sortable'  );
                wp_enqueue_script( 'jquery-ui-datepicker' );

                wp_enqueue_script( 'group-fields', plugins_url( 'js/main.js', __FILE__ ), array( 'jquery' ) );

                wp_enqueue_style( 'group-fields', plugins_url( 'css/style.css', __FILE__ ) );

                wp_enqueue_style( 'xprofile-admin-css', buddypress()->plugin_url . 'bp-xprofile/admin/css/admin.css', array(), bp_get_version() );
            }
        }

        public function wp_enqueue_scripts( $hook )
        {
            if( bp_is_current_component( 'groups' ) && bp_is_group_admin_screen( bp_action_variable() ) )
            {
                wp_enqueue_style( 'group-other-fields', plugins_url( 'css/other-fields.css', __FILE__ ) );
            }
        }

        public function register_extend_field( )
        {
            $labels = array(
                'name'               => empty( $_GET[ 'pasrent' ] ) ? _x( 'Group Field', 'post type general name' ) : _x( 'Field', 'post type general name' ),
                'singular_name'      => _x( 'group-fields', 'post type singular name' ),
                'add_new_item'       => empty( $_GET[ 'parent' ] ) ? __( 'Add New Field Group' ) : __( 'Add New Field' ),
                'edit_item'          => empty( $_GET[ 'parent' ] ) ? __( 'Edit Field Group' ) : __( 'Edit Field' )
            );

            $args = array(
                'labels'             => $labels,
                'description'        => __( 'Group Fields.' ),
                'public'             => true,
                'publicly_queryable' => true,
                'query_var'          => true,
                'capability_type'    => 'post',
                'hierarchical'       => true,
                //'show_ui'            => false,
                'show_in_menu'       => false,
                'supports'           => array( 'title' )
            );

            register_post_type( 'group-fields', $args );
        }

        public function post_fields_save( $post_id, $post, $update )
        {
            if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;

            if( !empty( $post->post_status ) && $post->post_status == 'auto-draft' ) return $post_id;

            if( $post->post_type == 'group-fields' )
            {
                if( !empty( $_POST[ 'group_description' ] ) )
                {
                    update_post_meta( $post_id, 'group_description', $_POST[ 'group_description' ] );
                }

                if( isset( $_POST[ 'required' ] ) )
                {
                    if( $_POST[ 'required' ] == 1 ) update_post_meta( $post_id, 'required', $_POST[ 'required' ] );

                    else delete_post_meta( $post_id, 'required' );
                }

                if( !empty( $_POST[ 'gf_advanced_fields' ] ) )
                {
                    update_post_meta( $post_id, 'gf_advanced_fields', $_POST[ 'gf_advanced_fields' ] );
                }

                if( !empty( $_POST[ 'fieldtype' ] ) )
                {
                    $data_field = array();

                    $data_field[ 'fieldtype' ] = $_POST[ 'fieldtype' ];

                    if( !empty( $_POST[ 'type' ] ) )
                    {
                        $data_field[ 'type' ] = $_POST[ 'type' ];

                        if( !empty( $_POST[ 'item_option' ] ) && $data_field[ 'type' ] == 'multi' )
                        {
                            $data_field[ 'options' ] = $_POST[ 'item_option' ];
                        }

                        if( !empty( $_POST[ 'default_option' ] ) )
                        {
                            $default_option = $_POST[ 'default_option' ];

                            if( !is_array( $default_option ) )
                            {
                                $default_option = array( 1 => $default_option );
                            }

                            if( !empty( $data_field[ 'options' ] ) )
                            {
                                foreach( $data_field[ 'options' ] as $i => $df )
                                {
                                    if( isset( $default_option[ $i ] ) ) $default_option[ $i ] = $df;
                                }
                            }

                            $data_field[ 'default_option' ] = $default_option;
                        }

                        if( !empty( $_POST[ 'sort_order' ] ) )
                        {
                            $data_field[ 'sort_order' ] = $_POST[ 'sort_order' ];
                        }
                    }

                    update_post_meta( $post_id, 'fieldtype', $data_field );
                }

                $redirect_url = menu_page_url( 'group-fields', false );

                if( $post->post_parent == 0 ) $redirect_url .= '#tabs-' . $post_id;

                else $redirect_url .= '#tabs-' . $post->post_parent;

                wp_redirect( $redirect_url ); exit;
            }
        }

        public function post_fields_delete( $id )
        {
            $the_post = get_post( $post_id );

            $deleted_post_type = $the_post->post_type;

            if( $deleted_post_type == 'group-fields' )
            {
                wp_redirect( menu_page_url( 'group-fields', false ) ); exit();
            }
        }

        public function group_reorder_fields( )
        {
            check_ajax_referer( 'reorder-fields', '_wpnonce_reorder_fields' );

            $order_fields = !empty( $_REQUEST[ 'field_order' ] ) ? $_REQUEST[ 'field_order' ] : '';

            if( !empty( $order_fields ) )
            {
                foreach( $order_fields as $i => $field )
                {
                    update_post_meta( $field, '__order', $i );
                }
            }

            die;
        }

        public static function rendering_field( $data, $field, $front = false )
        {
            $output = '';

            $type = !empty( $data[ 'fieldtype' ] ) ? $data[ 'fieldtype' ] : '';

            if( $type == 'textbox' || $type == 'email' || $type == 'url' )
            {
                $output = self::rendering_textbox( $data, $field, $front );
            }
            else if( $type == 'textarea' )
            {
                $output = self::rendering_textarea( $data, $field, $front );
            }
            else if( $type == 'datebox' )
            {
                $output = self::rendering_datebox( $data, $field, $front );
            }
            else if( $type == 'checkbox' || $type == 'radio' )
            {
                $output = self::rendering_checkbox( $data, $field, $front );
            }
            else if( $type == 'selectbox' || $type == 'multiselectbox' )
            {
                $output = self::rendering_selectbox( $data, $field, $front );
            }

            return $output;
        }

        public static function rendering_textbox( $data, $field, $front )
        {
            $output = sprintf( '<label for="%s" class="screen-reader-text">%s</label>', $field->post_name, $data[ 'fieldtype' ] );

            $value = '';

            if( $front )
            {
                $output = ''; $value = groups_get_groupmeta( $front, $field->post_name, true );
            }

            $output .= sprintf( '<input id="%s" placeholder="%s" name="%s" type="text" value="%s" %s/>', $field->post_name, $field->post_title, $field->post_name, $value, !empty( $data[ 'attributes' ] ) ? implode( ' ', $data[ 'attributes' ] ) : '' );

            return $output;
        }

        public static function rendering_textarea( $data, $field, $front )
        {
            $value = '';

            if( $front )
            {
                $output = ''; $value = groups_get_groupmeta( $front, $field->post_name, true );
            }

            $output = sprintf( '<textarea id="%s" name="%s" placeholder="%s" cols="40" rows="5" %s>%s</textarea>', $field->post_name, $field->post_name, $field->post_title, !empty( $data[ 'attributes' ] ) ? implode( ' ', $data[ 'attributes' ] ) : '', $value );

            return $output;
        }

        public static function rendering_datebox( $data, $field, $front )
        {
            $value = '';

            if( $front )
            {
                $output = ''; $value = groups_get_groupmeta( $front, $field->post_name, true );
            }

            $output .= sprintf( '<input id="%s" class="datebox" name="%s" type="text" %s/>', $field->post_name, $field->post_name, !empty( $data[ 'attributes' ] ) ? implode( ' ', $data[ 'attributes' ] ) : '' );

            return $output;
        }

        public static function rendering_checkbox( $data, $field, $front )
        {
            $value = '';

            if( $front ) $value = groups_get_groupmeta( $front, $field->post_name, true );

            $output = sprintf( '<div id="%s" class="input-options checkbox-options">', $field->post_name );

            if( !empty( $data[ 'options' ] ) )
            {
                foreach( $data[ 'options' ] as $i => $option )
                {
                    if( empty( $value ) )
                    {
                        $default = !empty( $data[ 'default_option' ][ $i ] ) ? $data[ 'default_option' ][ $i ] : '';

                        $checked = checked( $default, $option, false );
                    }
                    else
                    {
                        if( is_array( $value ) )
                        {
                            $checked = in_array( $option, $value ) ? 'checked="checked"' : '';
                        }
                        else
                        {
                            $checked = checked( $value, $option, false );
                        }
                    }

                    if( $front && $data[ 'fieldtype' ] == 'radio' )
                    {
                        $output .= sprintf( '<label for="%s[%s]" class="option-label" style="margin-right: 10px;"><input %s type="%s" name="%s" id="%s[%s]" value="%s" %s><span class="ml-10">%s</span></label>', $field->post_name, $i, $checked, $data[ 'fieldtype' ], $field->post_name, $field->post_name, $i, $option, !empty( $data[ 'attributes' ] ) ? implode( ' ', $data[ 'attributes' ] ) : '', $option );
                    }
                    else
                    {
                        $output .= sprintf( '<label for="%s[%s]" class="option-label" style="margin-right: 10px;"><input %s type="%s" name="%s[%s]" id="%s[%s]" value="%s" %s><span class="ml-10">%s</span></label>', $field->post_name, $i, $checked, $data[ 'fieldtype' ], $field->post_name, $i, $field->post_name, $i, $option, !empty( $data[ 'attributes' ] ) ? implode( ' ', $data[ 'attributes' ] ) : '', $option );
                    }
                }
            }

            $output .= '</div>';

            return $output;
        }

        public static function rendering_selectbox( $data, $field, $front )
        {
            $output = sprintf( '<label for="%s" class="screen-reader-text">%s</label>', $field->post_name, $data[ 'fieldtype' ] );

            $value = '';

            if( $front )
            {
                $output = ''; $value = groups_get_groupmeta( $front, $field->post_name, true );
            }

            $multiple = $data[ 'fieldtype' ] == 'multiselectbox' ? 'multiple="multiple"' : '';

            $array = $data[ 'fieldtype' ] == 'multiselectbox' ? '[]' : '';

            $output .= sprintf( '<select id="%s" name="%s%s" %s %s>', $field->post_name, $field->post_name, $array, $multiple, !empty( $data[ 'attributes' ] ) ? implode( ' ', $data[ 'attributes' ] ) : '' );

            if( $data[ 'fieldtype' ] !== 'multiselectbox' ) $output .= '<option value>----</option>';

            if( !empty( $data[ 'options' ] ) )
            {
                foreach( $data[ 'options' ] as $i => $option )
                {
                    if( empty( $value ) )
                    {
                        $default = !empty( $data[ 'default_option' ][ $i ] ) ? $data[ 'default_option' ][ $i ] : '';

                        $selected = selected( $default, $option, false );
                    }
                    else
                    {
                        if( is_array( $value ) )
                        {
                            $selected = in_array( $option, $value ) ? 'selected="selected"' : '';
                        }
                        else
                        {
                            $selected = selected( $value, $option, false );
                        }
                    }

                    $output .= sprintf( '<option data-def="%s" value="%s" %s>%s</option>', $default, $option, $selected, $option );
                }
            }

            $output .= '</select>';

            return $output;
        }

        public function groups_custom_group_fields_editable( )
        {
            $group_id = bp_get_group_id( );

            $group_fields = get_posts( array( 'post_type' => 'group-fields', 'post_parent' => 0, 'numberposts' => 1 ) );

            if( !empty( $group_fields ) )
            {
                foreach( $group_fields as $group_field )
                {
                    $fields = self::getItems( array( 'post_parent' => $group_field->ID ) );

                    foreach( $fields as $field )
                    {
                        $data = get_post_meta( $field->ID, 'fieldtype', true );

                        echo '<label for="'.$field->post_name.'">'.$field->post_title.'</label>';

                        echo $this->rendering_field( $data, $field->post_name, $group_id );
                    }
                }
            }
        }

        public static function get_post_parent( )
        {
            $group_id = bp_get_group_id( );

            $actions = self::get_action_variables( );

            if( !empty( $actions[ 1 ] ) && $actions[ 0 ] == 'other-fields' )
            {
                $name = $actions[ 1 ];

                $groups = get_posts( array( 'post_type' => 'group-fields', 'name' => $name, 'post_parent' => 0, 'numberposts' => 1 ) );
            }
            elseif( $actions[ 0 ] == 'other-fields' )
            {
                $groups = self::getItems( );
            }

            if( !empty( $groups[ 0 ] ) ) return $groups[ 0 ];

            else return false;
        }

        public static function getFields( $echo )
        {
            $group = self::get_post_parent( );

            if( !empty( $group ) )
            {
                $fields = self::getItems( array( 'post_parent' => $group->ID ) );

                if( $echo )
                {
                    foreach( $fields as $field ) echo self::getField( $field, $group_id );
                }

                else return $fields;
            }

            return false;
        }

        public static function getItems( $args = array() )
        {
            $defaults = array(
                'post_type' => 'group-fields',
                'post_status' => 'publish',
                'post_parent' => 0,
                'orderby' => '__order',
                'order' => 'ASC',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => '__order',
                        'compare' => 'EXISTS'
                    ),
                    array(
                        'key' => '__order',
                        'compare' => 'NOT EXISTS'
                    )
                ),
                'numberposts' => -1
            );

            $args = wp_parse_args( $args, $defaults );

            $items = get_posts( $args );

            return $items;
        }

        public static function getField( $_field, $group_id )
        {
            $data = get_post_meta( $_field->ID, 'fieldtype', true ); $attributes = [];

            $settings = get_post_meta( $_field->ID, 'gf_advanced_fields', true );

            $required = get_post_meta( $_field->ID, 'required', true ); ?>

            <?php $class = array( 'editfield', 'field_type_' . $data[ 'fieldtype' ], 'field_' . $_field->post_name ); ?>

            <?php

                if( !empty( $required ) )
                {
                    $class[] = 'required-field'; $attributes[] = 'aria-required="true"';
                }

                if( !empty( $settings[ 'field_class' ] ) )
                {
                    $attributes[] = 'class="' . $settings[ 'field_class' ] . '"';
                }

                if( !empty( $settings[ 'meta_key' ] ) )
                {
                    $attributes[] = 'data-key="' . $settings[ 'meta_key' ] . '"';
                }

                if( !empty( $settings[ 'data_validation' ] ) )
                {
                    $attributes[] = 'data-validation="' . $settings[ 'data_validation' ] . '"';
                }

                if( !empty( $settings[ 'validation_format' ] ) )
                {
                    $attributes[] = 'data-validation-format="' . $settings[ 'validation_format' ] . '"';
                }

                $data[ 'attributes' ] = $attributes;

            ?>

            <?php if( !empty( $settings[ 'container_class' ] ) ) $class[] = $settings[ 'container_class' ]; ?>

            <?php printf( '<div id="%s" class="%s">', $settings[ 'container_id' ], implode( ' ', $class ) ); ?>

                <label for="<?php echo $_field->post_name; ?>"><?php echo $_field->post_title; ?></label>

                <?php echo self::rendering_field( $data, $_field, $group_id ); ?>

            </div>

            <?php
        }

        public function group_fields_save( $group_id )
        {
            global $bp, $wpdb;

            $post_parent = !empty( $_POST[ 'post_parent' ] ) ? $_POST[ 'post_parent' ] : FALSE;

            if( !empty( $post_parent ) )
            {
                $plain_fields = $wpdb->get_results( "SELECT $wpdb->posts.* FROM $wpdb->posts WHERE $wpdb->posts.post_type = 'group-fields' AND $wpdb->posts.post_parent = $post_parent", OBJECT );

                $success = true;

                foreach( $plain_fields as $field )
                {
                    $key = $field->post_name;

                    if( !empty( $_POST[ $key ] ) )
                    {
                        $value = $_POST[ $key ];

                        $retval = groups_update_groupmeta( $group_id, $key, $value );
                    }
                    else groups_update_groupmeta( $group_id, $key, '' );
                }

                if( !empty( $_POST[ 'extend_section' ] ) && wp_verify_nonce( $_POST[ 'extend_section' ], 'extend-section' ) )
                {
                    if( $success ) bp_core_add_message( __( 'Group details were successfully updated.' ), 'success' );

                    else bp_core_add_message( __( 'There was an error updating group details. Please try again.' ), 'error' );

                    do_action( 'updated_details_group', $group_id );

                    wp_redirect( $_POST[ '_wp_http_referer' ] ); die;
                }
            }
        }

        public static function bp_group_admin_links( )
        {
            return get_posts(
                array(
                    'post_type' => 'group-fields',
                    'post_parent' => 0,
                    'numberposts' => -1,
                    'post_status' => 'publish',
                    'order' => 'ASC'
                )
            );
        }

        public static function get_action_variables( )
        {
            global $bp;

            return $bp->canonical_stack[ 'action_variables' ];
        }

        public static function get_group_variable( )
        {
            global $bp;

            $variables = $bp->canonical_stack[ 'action_variables' ];

            if( bp_is_action_variable( 'group' ) && bp_is_group_admin_screen( 'edit-details' ) )
            {
                if( $variables[ 1 ] == 'group' && $variables[ 0 ] == 'edit-details' && !empty( $variables[ 2 ] ) )
                {
                    return $variables[ 2 ];
                }
            }

            return false;
        }

        public function bp_group_get_field_groups( )
        {
            $groups = wp_cache_get( 'all', 'bp_groups_extend_field_groups' );
            
            if( false === $groups )
            {    
                $groups = self::bp_groups_field_get_groups( array( 'fetch_fields' => true ) );
                
                wp_cache_set( 'all', $groups, 'bp_groups_extend_field_groups' );
            }

            return apply_filters( 'bp_profile_get_field_groups', $groups );
        }

        public function bp_get_group_group_name( )
        {
            // Check action variable.
            $group_id = bp_action_variable( 1 );

            // Check for cached group.

            $groups = self::getItems( );

            foreach( $groups as $item ) if( $item->post_name == $group_id ) $name = $item->post_title;

            if( empty( $name ) && !empty( $groups[ 0 ] ) ) $name = $groups[ 0 ]->post_title;

            return apply_filters( 'bp_get_profile_group_name', $name, $group_id );
        }

        public function bp_groups_field_get_groups( $args = array() )
        {
            $groups = self::bp_group_admin_links( );

            return apply_filters( 'bp_groups_field_get_groups', $groups, $args );
        }

    }

    function SG_extend_fields_group( )
    {
        if( bp_is_active( 'groups' ) )
        {
            return new Bp_Extended_Group_Fields( );
        }
    }

    add_action( 'bp_init', 'SG_extend_fields_group' );

    add_action( 'bp_setup_nav', 'add_groups_extend_fields_page' );

    function add_groups_extend_fields_page( )
    {
        global $bp;

        if( bp_is_current_component( 'groups' ) )
        {
            if( bp_is_item_admin( ) )
            {
                $group_link = bp_get_group_permalink( $bp->groups->current_group );

                $admin_link = trailingslashit( $group_link . 'admin' );

                bp_core_new_subnav_item( 
                    array(
                        'user_has_access'   => bp_is_item_admin(),
                        'show_in_admin_bar' => true,
                        'screen_function' => 'groups_screen_group_other_fields',
                        'name' => __( 'Other Fields', 'buddypress' ),
                        'slug' => 'other-fields',
                        'parent_url' => $admin_link,
                        'parent_slug' => $bp->groups->current_group->slug . '_manage',
                        'position' => 10 
                    )
                );
            }
        }
    }

    /**
     * Handle the display of a group's admin/edit-details page.
     *
     * @since 1.0.0
     */
    function groups_screen_group_admin_other_fields( )
    {
        if( 'other-fields' != bp_get_group_current_admin_tab() )
            return false;

        if( bp_is_item_admin() )
        {
            $bp = buddypress( );

            // If the edit form has been submitted, save the edited details.
            if( isset( $_POST[ 'save' ] ) )
            {
                Bp_Extended_Group_Fields::group_fields_save( $bp->groups->current_group->id );
            }

            bp_core_load_template( 'groups/single/home' );
        }
    }

    add_action( 'bp_screens', 'groups_screen_group_admin_other_fields' );

    function bp_plugin_add_template_stack( $templates )
    {
        if( bp_is_current_component( 'groups' ) && bp_is_group_admin_screen( bp_action_variable() ) )
        {
            $templates[] = plugin_dir_path( __FILE__ ) . '/templates/';
        }
     
        return $templates;
    }
     
    add_filter( 'bp_get_template_stack', 'bp_plugin_add_template_stack', 10, 1 );

    function bp_groups_has_multiple_field_groups( )
    {
        $has_multiple_groups = count( (array) Bp_Extended_Group_Fields::bp_group_get_field_groups() ) > 1;

        return (bool) apply_filters( 'bp_groups_has_multiple_field_groups', $has_multiple_groups );
    }

    function bp_extend_fields_group_tabs( )
    {
        echo bp_get_extend_fields_group_tabs( );
    }

    function bp_get_extend_fields_group_tabs( )
    {
        global $bp;

        $group_link = bp_get_group_permalink( $bp->groups->current_group );

        $admin_link = trailingslashit( $group_link . 'admin' );

        // Get field group data.
        $groups     = Bp_Extended_Group_Fields::bp_group_get_field_groups( );
        
        $group_name = Bp_Extended_Group_Fields::bp_get_group_group_name();
        
        $tabs       = array();

        // Loop through field groups and put a tab-lst together.
        for ( $i = 0, $count = count( $groups ); $i < $count; ++$i ) {

            // Setup the selected class.
            $selected = '';
            if ( $group_name === $groups[ $i ]->post_title ) {
                $selected = ' class="current"';
            }

            // Build the profile field group link.

            $link   = trailingslashit( $admin_link . 'other-fields/' . $groups[ $i ]->post_name );

            // Add tab to end of tabs array.
            $tabs[] = sprintf(
                '<li %1$s><a href="%2$s">%3$s</a></li>',
                $selected,
                esc_url( $link ),
                esc_html( apply_filters( 'bp_get_the_profile_group_name', $groups[ $i ]->post_title ) )
            );
        }

        $tabs = apply_filters( 'xprofile_filter_profile_group_tabs', $tabs, $groups, $group_name );

        return join( '', $tabs );
    }


endif;
