<?php if( bp_groups_has_multiple_field_groups( ) ) : ?>
	
	<ul class="button-nav" aria-label="<?php esc_attr_e( 'Group field groups', 'buddypress' ); ?>" role="navigation">

		<?php bp_extend_fields_group_tabs( ); ?>

	</ul>

<?php endif ;?>

<div class="clear"></div>

<?php $parent = Bp_Extended_Group_Fields::get_post_parent(); ?>

<input type="hidden" id="post_parent" name="post_parent" value="<?php echo !empty( $parent->ID ) ? $parent->ID : null;  ?>">

<?php $ex_fields = Bp_Extended_Group_Fields::getFields( false ); ?>

<?php if( !empty( $ex_fields ) ): $group_id = bp_get_group_id( ); ?>

	<p>

		<?php foreach( $ex_fields as $ex_field ): ?>

			<?php Bp_Extended_Group_Fields::getField( $ex_field, $group_id ); ?>

		<?php endforeach; ?>

	</p>

	<p><input type="submit" value="<?php esc_attr_e( 'Save Changes', 'buddypress' ); ?>" id="save" name="save" /></p>
	
	<?php wp_nonce_field( 'extend-section', 'extend_section' ); ?>

<?php endif; ?>

