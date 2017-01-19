<?php //print_r( $data ); ?>

    <input type="hidden" name="type" id="type" value="<?php echo $type; ?>">

    <select name="fieldtype" id="fieldtype">
        <optgroup label="Multi Fields" data-type="multi">
            <option value="checkbox" <?php selected( $fieldtype, 'checkbox' ); ?>>
                <?php _e( 'Checkboxes' ); ?>        
            </option>
            <option value="selectbox" <?php selected( $fieldtype, 'selectbox' ); ?>>
                <?php _e( 'Drop Down Select Box' ); ?>
            </option>
            <option value="multiselectbox" <?php selected( $fieldtype, 'multiselectbox' ); ?>>
                <?php _e( 'Multi Select Box' ); ?>
            </option>
            <option value="radio" <?php selected( $fieldtype, 'radio' ); ?>>
                <?php _e( 'Radio Buttons' ); ?>        
            </option>
        </optgroup>
        <optgroup label="Single Fields" data-type="single">
        <?php /*    <option value="datebox" <?php selected( $fieldtype, 'datebox' ); ?>>
                <?php _e( 'Date Selector' ); ?>
            </option> */ ?>
            <option value="textarea" <?php selected( $fieldtype, 'textarea' ); ?>>
                <?php _e( 'Multi-line Text Area' ); ?>
            </option>
            <?php /*<option value="number" <?php selected( $fieldtype, 'number' ); ?>>
                <?php _e( 'Number' ); ?>
            </option> */?>
            <option value="textbox" <?php selected( $fieldtype, 'textbox' ); ?>>
                <?php _e( 'Text Box' ); ?>
            </option>
           <?php /* <option value="url" <?php selected( $fieldtype, 'url' ); ?>>
                <?php _e( 'URL' ); ?>        
            </option> */?>
        </optgroup>
    </select>

    <div id="multi-options" class="postbox bp-options-box" style="margin-top: 15px;<?php echo $type == 'multi' ? '' : 'display:none;'; ?>">
        
        <h3><?php _e( 'Please enter options for this Field:' ); ?></h3>
        
        <div class="inside">
            
            <p>
                <label for="sort_order">Sort Order:</label>
                <select name="sort_order" >
                    <option value="custom" <?php selected( $data[ 'sort_order' ], 'custom' ); ?>>Custom</option>
                    <option value="asc" <?php selected( $data[ 'sort_order' ], 'asc' ); ?>>Ascending</option>
                    <option value="desc" <?php selected( $data[ 'sort_order' ], 'desc' ); ?>>Descending</option>
                </select>
            </p>

            <?php if( !empty( $data[ 'options' ] ) ): ?>

                <?php reset( $data[ 'options' ] ); $first = key( $data[ 'options' ] ); ?>

                <?php foreach( $data[ 'options' ] as $i => $option ): ?>

                    <div class="bp-option sortable" style="cursor: default;">
                            
                        <span class="bp-option-icon grabber" style="cursor: default;"></span>

                        <label for="item_option[<?php echo $i; ?>]" class="screen-reader-text">Add an option</label>

                        <input type="text" name="item_option[<?php echo $i; ?>]" id="item_option[<?php echo $i; ?>]" value="<?php echo $option; ?>">

                        <label for="default_option[<?php echo $i; ?>]">

                            <?php $default = !empty( $data[ 'default_option' ][ $i ] ) ? $data[ 'default_option' ][ $i ] : ''; ?>

                            <?php if( $fieldtype == 'checkbox' || $fieldtype == 'multiselectbox' ): ?>

                                <input type="checkbox" id="default_option[<?php echo $i; ?>]" name="default_option[<?php echo $i; ?>]" <?php checked( $default, $option ); ?> value="<?php echo $option; ?>">

                            <?php else: ?>

                                <input type="radio" id="default_option[<?php echo $i; ?>]" name="default_option[<?php echo $i; ?>]" <?php checked( $default, $option ); ?> value="<?php echo $option; ?>">

                            <?php endif; ?>
                            
                            Default Value                           
                        
                        </label>

                        <?php if( $i !== $first ): ?>

                            <div class="delete-button"><a href="#" class="delete">Delete</a></div>

                        <?php endif; ?>               
                    
                    </div> 

                <?php endforeach; ?>

            <?php endif; ?>          
                
            <div id="options_more"></div>

            <p><a href="#" id="add-option">Add Another Option</a></p>

        </div>
    
    </div>
