

// build config page for custom-field link
$(document).ready(
	function() {

		$("#target_custom_field").bind("change", function() {

			if($(this).val() != "") {

				// hide all options, that don't belong to selected category
				$("select[name!='target_custom_field'] option[name!='target_candidate_" + $(this).val() + "_option']").hide();

				// show all options, that belong to selected category
				$("select[name!='target_custom_field'] option[name='target_candidate_" + $(this).val() + "_option']").show();

			} else {
				// hide all options
				$("select[name!='target_custom_field'] option").hide();
			}

			// update options, if chosen-plugin is available
			// see https://harvesthq.github.io/chosen/
			$("select[name!='target_custom_field']").trigger("chosen:updated");

		});


		// apply selection when loading the page
		$("#target_custom_field").change();



	}
);
