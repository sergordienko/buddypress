<div class="wrap">
	
	<h1>

		<?php _e( 'Group Fields' ); ?>

		<a id="add_group" class="add-new-h2" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=group-fields' ) ); ?>">

			<?php _e( 'Add New Field Group' ); ?>

		</a>
			
	</h1>
	
	<form id="group-field-form" method="post">

		<?php if( user_can( $user->ID, 'manage_options' ) ): ?>

			<input type="hidden" id="_wpnonce_reorder_fields" name="_wpnonce_reorder_fields" value="<?php echo wp_create_nonce( 'reorder-fields' ); ?>" />

		<?php endif; ?>
		
		<div id="tabs" class="">
			
			<?php $group_fields = get_posts( array( 'post_type' => 'group-fields', 'post_status' => 'publish', 'numberposts' => -1, 'post_parent' => 0, 'order' => 'ASC' ) ); ?>

			<?php if( !empty( $group_fields ) ): ?>

				<ul id="field-group-tabs" style="display: block;">

				<?php foreach( $group_fields as $group_field ): ?>

					<li id="group_<?php echo $group_field->ID; ?>" data-group="<?php echo $group_field->ID; ?>">
						
						<a href="#tabs-<?php echo $group_field->ID; ?>" class="ui-tab">
						
							<?php echo $group_field->post_title; ?>							
						
						</a>
					
					</li>

				<?php endforeach; ?>

				</ul>

				<div id="content-tabs" class="hide">

				<?php foreach( $group_fields as $group_field ): ?>

					<div id="tabs-<?php echo $group_field->ID; ?>">

						<div class="tab-toolbar">
							<div class="tab-toolbar-left">
								<a class="button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=group-fields&parent=' . $group_field->ID ) ); ?>"><?php _e( 'Add New Field' ); ?></a>
								<a class="button edit" href="<?php echo esc_url( admin_url( 'post.php?post=' . $group_field->ID . '&action=edit' ) ); ?>"><?php _e( 'Edit Group' ); ?></a>
									<div class="delete-button">
										<a class="confirm submitdelete deletion ajax-option-delete" href="<?php echo add_query_arg( array( 'mode' => 'delete_group', 'group_id' => $group_field->ID ), menu_page_url( 'group-fields', false ) ); ?>">
											<?php _e( 'Delete Group' ); ?>
										</a>
									</div>
							</div>
						</div>

						<?php

							$args = array(
							    'post_type' => 'group-fields',
							    'post_status' => 'publish',
							    'post_parent' => $group_field->ID,
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

							$fields = get_posts( $args );

						?>
							
						<fieldset class="connectedSortable field-group ui-sortable">

							<legend class="screen-reader-text">

								<?php printf( __( 'Fields for "%s" Group' ), $group_field->post_title ); ?>

							</legend>

							<?php if( empty( $fields ) ): ?>

								<p class="nodrag nofields"><?php _e( 'There are no fields in this group.' ); ?></p>

							<?php else: ?>

								<?php foreach( $fields as $field ): ?>

									<?php $data = get_post_meta( $field->ID, 'fieldtype', true ); ?>

									<fieldset id="<?php echo $field->ID; ?>" class="sortable <?php echo $data[ 'fieldtype' ]; ?>">

										<legend>
											<span>
												<?php echo $field->post_title; ?>
												<?php $required = get_post_meta( $field->ID, 'required', true ); ?>
												<?php if( $required == '1' ): ?>
									 				<span class="bp-required-field-label">(required)</span>
									 			<?php endif; ?>		
											</span>
										</legend>

										<div class="field-wrapper">

											<?php echo self::rendering_field( $data, $field ); ?>
											
											<div class="actions">
												<a class="button edit" href="<?php echo esc_url( add_query_arg( array( 'parent' => $field->post_parent ), admin_url( 'post.php?post=' . $field->ID . '&action=edit' ) ) ); ?>"><?php _e( 'Edit' ); ?></a>
												<div class="delete-button">
													<?php $delete_url = add_query_arg( array( 'mode' => 'delete_field', 'field_id' => $field->ID ), menu_page_url( 'group-fields', false ) ); ?>
													<?php $delete_url .= '#tabs-' . $field->post_parent; ?>
													<a class="confirm submit-delete deletion" href="<?php echo $delete_url; ?>"><?php _e( 'Delete' ); ?></a>
												</div>
											</div>
										
										</div>
												
									</fieldset>

								<?php endforeach; ?>

							<?php endif; ?>
									
						</fieldset>

					</div>

				<?php endforeach; ?>

				</div>

			<?php endif; ?>

		</div>
	
	</form>

</div>
