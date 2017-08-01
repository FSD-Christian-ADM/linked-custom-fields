<?php
# Copyright (c) 2011 Robert Munteanu (robert@lmn.ro)

# Linked custom fields for MantisBT is free software:
# you can redistribute it and/or modify it under the terms of the GNU
# General Public License as published by the Free Software Foundation,
# either version 2 of the License, or (at your option) any later version.
#
# Linked custom fields plugin for MantisBT is distributed in the hope
# that it will be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Linked custom fields plugin for MantisBT.
# If not, see <http://www.gnu.org/licenses/>.

    #header ("Content-Type: text/javascript");

	require_once( 'core.php' );


	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

    layout_page_header( plugin_lang_get( 'title' ) );
    layout_page_begin( 'manage_overview_page.php' );

	$f_custom_field = custom_field_get_definition( gpc_get_int('custom_field_id') );
	$t_linked_custom_field_id = LinkedCustomFieldsDao::getLinkedFieldId( $f_custom_field['id'] );
	
	$t_target_candidates = array();
	
    $t_custom_fields = custom_field_get_ids();
    foreach ( $t_custom_fields as $t_custom_field ) {
        
        $t_custom_field_def = custom_field_get_definition( $t_custom_field );
        if ( $t_custom_field_def['type'] != CUSTOM_FIELD_TYPE_ENUM && $t_custom_field_def['type'] != CUSTOM_FIELD_TYPE_MULTILIST ) {
            continue;
        }
        
        $t_target_candidates[] = $t_custom_field_def;
    }

    $t_linked_values = array();
    $t_linked_values_raw = LinkedCustomFieldsDao::getLinkedValuesMap( $f_custom_field['id'] );

    // create an associative array to mark selected entries
    foreach($t_linked_values_raw as $t_linked_value) {

        $t_linked_values[$t_linked_value[0]] = $t_linked_value[1];

    }

?>

<form method="post" action="<?php echo plugin_page('configure_custom_field_link_update.php') ?>">
<?php echo form_security_field( 'configure_custom_field_link' ) ?>
<br />
<input type="hidden" name="custom_field_id" id="custom_field_id" value="<?php echo gpc_get_int('custom_field_id')?>" />
    <div class="widget-box widget-color-blue2">

        <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter"> <?php echo plugin_lang_get( 'title' ); ?> </h4>
        </div>

        <div class="widget-body">
            <div class="widget-main no-padding">
                <table class="table table-bordered table-condensed table-striped">
                    <tbody>
                        <tr <?php echo helper_alternate_class() ?>>
                            <td><?php echo plugin_lang_get('custom_field') ?>: </td>
                            <td style="padding-left: 7px; font-weight:bold;"><?php echo $f_custom_field['name'] ?></td>
                        </tr>
                        <tr <?php echo helper_alternate_class() ?>>
                            <td><?php echo plugin_lang_get('linked_to') ?></td>
                            <td>
                                <select id="target_custom_field" name="target_custom_field">
                                    <option value="">None</option>

                                <?php

                                    foreach  ( $t_target_candidates as $t_target_candidate ) {

                                        if ( $t_target_candidate['id'] == $f_custom_field['id'] ) {
                                            continue;
                                        }

                                        $t_selected = $t_linked_custom_field_id == $t_target_candidate['id'] ? ' selected="selected"' : "";

                                        echo '<option' . $t_selected . ' value="' . $t_target_candidate['id'] .'">'.$t_target_candidate['name'].'</option>';

                                     }

                                ?>

                                </select>
                            </td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



    <div class="widget-box widget-color-blue2">

        <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter"> <?php echo plugin_lang_get( 'title' ); ?> </h4>
        </div>

        <div class="widget-body">
            <div class="widget-main no-padding">
                <table class="table table-bordered table-condensed table-striped" id="link-area">
                    <thead>
                        <tr class="category">
                            <th><?php echo plugin_lang_get('source_field_value')?></th>
                            <th><?php echo plugin_lang_get('target_field_values')?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( explode('|', $f_custom_field['possible_values'] ) as $t_idx =>  $t_possible_value ) { ?>
                            <tr <?php echo helper_alternate_class() ?>>
                                <td> <?php echo $t_possible_value ?></td>
                                <td><select id="custom_field_linked_values_<?php echo $t_idx?>"
                                            name="custom_field_linked_values_<?php echo $t_idx?>[]"
                                            multiple="multiple">
                                        <?php

                                            foreach($t_target_candidates as $t_target_candidate) {
                                                $candidate_options = explode("|",$t_target_candidate["possible_values"]);


                                                foreach($candidate_options as $candidate_option) {

                                                    echo "<option name='target_candidate_".$t_target_candidate["id"]."_option'";

                                                    if(in_array($candidate_option, $t_linked_values[$t_possible_value])) {
                                                        echo " selected='selected' ";
                                                    }

                                                    echo ">".$candidate_option."</option>";
                                                }
                                            }

                                        ?>

                                    </select></td>
                            </tr>
                        <?php } ?>
                            <tr>
                                <td>
                                    &#160;
                                </td>
                                <td>
                                    <input type="submit" class="button" value="<?php echo plugin_lang_get( 'submit' ) ?>" />
                                </td>
                            </tr>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<?php

    layout_page_end();

?>