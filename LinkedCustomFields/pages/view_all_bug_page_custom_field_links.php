<?php
	header ("Content-Type: text/javascript");

	require_once 'core.php';


	$t_project_id = helper_get_current_project();

	$t_all_custom_field_ids = custom_field_get_linked_ids( $t_project_id );

?>

	var linkedFieldValues = {};
	var allFieldValues = {};
	var bindings = {};
	var savedValues = {};

<?php

	foreach ( $t_all_custom_field_ids as $t_custom_field_id ) {

		$t_has_write_access = custom_field_has_write_access_to_project( $t_custom_field_id, $t_project_id );

		if ( ! $t_has_write_access ) {
			continue;
		}

		$t_linked_values = LinkedCustomFieldsDao::getLinkedValuesMap( $t_custom_field_id );

		if ( count ( $t_linked_values ) > 0 ) {

			// values from $t_custom_field_id trigger filter values from $t_linked_field_id

			$t_linked_field_id = LinkedCustomFieldsDao::getLinkedFieldId( $t_custom_field_id );

			$t_linked_field = custom_field_get_definition( $t_linked_field_id );

			$t_saved_values_source_id = $t_master_bug_id != 0 ? $t_master_bug_id : $t_bug_id;
			$t_saved_values = $t_saved_values_source_id != 0 ? explode('|', custom_field_get_value(  $t_linked_field_id, $t_saved_values_source_id ) ) : array();

			echo 'savedValues["' . $t_linked_field_id .'"] = ' . JavascriptUtils::toJSArray( $t_saved_values ).";\n";
			echo 'bindings["' . $t_custom_field_id.'"] = "'. $t_linked_field_id.'";'."\n";
			echo 'allFieldValues["' .$t_custom_field_id.'"] = ' . JavascriptUtils::toJSArray( explode('|', $t_linked_field['possible_values']), false ).";\n";
			echo 'linkedFieldValues["'.$t_custom_field_id."\"] = {};\n";

			foreach ( $t_linked_values as $t_linked_value_arr ) {
				list($t_source_value, $t_target_values ) = $t_linked_value_arr;
				echo 'linkedFieldValues["'.$t_custom_field_id.'"]["'. $t_source_value.'"] = ' . JavascriptUtils::toJSArray( $t_target_values, false ).";\n";
			}
		}
	}
?>

var LinkedCustomFieldsUtil = {
	removeDuplicates : function(arr) {

		var uniques = [];
		for(var i=arr.length;i--;) {
			var val = arr[i];
			if(jQuery.inArray( val, uniques )===-1) {
				uniques.unshift(val);
			}
		}

		return uniques;

	},

	findCustomFieldByFieldId: function(fieldId) {

		var fieldRef = jQuery("[name^='custom_field_" + fieldId  + "']");

		return fieldRef;

	}

};

var refreshLinkedValues = function(fieldId, fieldValue) {

	var targetValues = [];

	if ( fieldValue instanceof Array) {
		for ( var i = 0 ; i < fieldValue.length; i++) {
			var singleValue = fieldValue[i];
			var currentTargetValues = linkedFieldValues[fieldId][singleValue] || [];

			for ( var j = 0 ; j < currentTargetValues.length; j++ ) {
				targetValues.push(currentTargetValues[j]);
			}
		}

		targetValues = LinkedCustomFieldsUtil.removeDuplicates(targetValues);

	} else {
		targetValues = linkedFieldValues[fieldId][fieldValue];
	}



	if ( ! targetValues || targetValues.length == 0 ) {
		targetValues = allFieldValues[fieldId] ;
	}

	var targetFieldId = bindings[fieldId];

	var targetFieldRef = LinkedCustomFieldsUtil.findCustomFieldByFieldId ( targetFieldId );

	// set size of target select to a fixed value, otherwise the layout flips after each update
	targetFieldRef.attr("style","width: 200px !important;");

	targetFieldRef.empty();
	for ( var i = 0 ; i < targetValues.length; i++ ) {
		var targetValue = targetValues[i];
		targetFieldRef.append(jQuery('<option/>').
			attr('value', targetValue).
			html(targetValue));
	}

	targetFieldRef.val(savedValues[targetFieldId]);

};

jQuery(document).ready(function() {

	for ( var boundKey in bindings ) {

		// add a mutation observer to check, when select element is added in filter

		// The node to be monitored
		var target = $("td#custom_field_"+boundKey+"_filter_target")[0];

		// Create an observer instance
		var observer = new MutationObserver(function( mutations ) {
			mutations.forEach(function( mutation ) {
			var newNodes = mutation.addedNodes; // DOM NodeList
			if( newNodes !== null ) { // If there are new nodes added
				var $nodes = $( newNodes ); // jQuery set
				$nodes.each(function() {
					var node = $( this );
					var nodeDOM = node.get(0);
					// skip the loading-text (it's a span) and wait for the added select
					if(nodeDOM.tagName == "SELECT") {

						// add event listener for linked custom fields

						var applicable = linkedFieldValues[boundKey];

						var customFieldRef = LinkedCustomFieldsUtil.findCustomFieldByFieldId( boundKey );

						customFieldRef.data('fieldId', boundKey);

						refreshLinkedValues(boundKey, customFieldRef.val());

						customFieldRef.change(function() {
							refreshLinkedValues(customFieldRef.data('fieldId'), customFieldRef.val());

						});
					}
				});
			}
		});
	});

	// Configuration of the observer:
	var config = {
		attributes: true,
		childList: true,
		characterData: true
	};

	// Pass in the target node, as well as the observer options
	observer.observe(target, config);

	// Later, you can stop observing
	//observer.disconnect();


	}
});
