var wcfd_settings = (function($, window, document) {
	var MSG_INVALID_NAME = 'NAME/ID must begin with a lowercase letter ([a-z]) and may be followed by any number of lowercase letters, digits ([0-9]) and underscores ("_")';
	
    $( "#wcfd_new_field_form_pp" ).dialog({
	  	modal: true,
		width: 500,
		//height: 400,
		resizable: false,
		autoOpen: false,
		buttons: [{
			text: "Add New Field",
			click: function() {
				var result = wcfd_add_new_row( this );
				if(result){
					$( this ).dialog( "close" );
				}
			}
		}]
	});
	
	$( "#wcfd_edit_field_form_pp" ).dialog({
	  	modal: true,
		width: 500,
		//height: 400,
		resizable: false,
		autoOpen: false,
		buttons: [{
			text: "Edit Field",
			click: function() {
				var result = wcfd_update_row( this );
				if(result){
					$( this ).dialog( "close" );
				}
			}
		}]
	});
	
	$('select.thwcfd-enhanced-multi-select').select2({
		minimumResultsForSearch: 10,
		allowClear : true,
		placeholder: $(this).data('placeholder')
	}).addClass('enhanced');
				
	$( ".wcfd_remove_field_btn" ).click( function() {
		var form =  $(this.form);		
		
		$('#wcfd_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			$(this).closest('tr').remove();
	  	});	  	
	});
	
	$('#wcfd_checkout_fields tbody').sortable({
		items:'tr',
		cursor:'move',
		axis:'y',
		handle: 'td.sort',
		scrollSensitivity:40,
		helper:function(e,ui){
			ui.children().each(function(){
				$(this).width($(this).width());
			});
			ui.css('left', '0');
			return ui;
		}
	});
	
	$("#wcfd_checkout_fields tbody").on("sortstart", function( event, ui ){
		ui.item.css('background-color','#f6f6f6');										
	});
	$("#wcfd_checkout_fields tbody").on("sortstop", function( event, ui ){
		ui.item.removeAttr('style');
		wcfd_prepare_field_order_indexes();
	});
	
	_openNewFieldForm = function openNewFieldForm(tabName){
		if(tabName == 'billing' || tabName == 'shipping' || tabName == 'additional'){
			tabName = tabName+'_';	
		}
		
		var form = $("#wcfd_new_field_form_pp");
		wcfd_clear_form(form);
		form.find("input[name=fname]").val(tabName);
		form.find("select[name=ftype]").change();
		form.find("input[name=fclass]").val('form-row-wide');
		
	  	$( "#wcfd_new_field_form_pp" ).dialog( "open" );
	}
	
	function wcfd_add_new_row(form){
		var name  = $(form).find("input[name=fname]").val();
		var type  = $(form).find("select[name=ftype]").val();
		var label = $(form).find("input[name=flabel]").val();
		var placeholder = $(form).find("input[name=fplaceholder]").val();
		var optionsList = $(form).find("input[name=foptions]").val();
		
		var fieldClass = $(form).find("input[name=fclass]").val();
		var labelClass = $(form).find("input[name=flabelclass]").val();
		
		var required = $(form).find("input[name=frequired]").prop('checked');
		var clearRow = $(form).find("input[name=fclearRow]").prop('checked');
		var enabled  = $(form).find("input[name=fenabled]").prop('checked');
		
		var showinemail = $(form).find("input[name=fshowinemail]").prop('checked');
		var showinorder = $(form).find("input[name=fshowinorder]").prop('checked');
		
		var validations = $(form).find("select[name=fvalidate]").val();
		
		var err_msgs = '';
		if(name == ''){
			err_msgs = 'Name is required';
		}else if(!isHtmlIdValid(name)){
			err_msgs = MSG_INVALID_NAME;
		}else if(type == ''){
			err_msgs = 'Type is required';
		}
		
		if(err_msgs != ''){
			$(form).find('.err_msgs').html(err_msgs);
			return false;
		}
				
		required = required ? 1 : 0;
		clearRow = clearRow ? 1 : 0;
		enabled  = enabled ? 1 : 0;
		
		showinemail = showinemail ? 1 : 0;
		showinorder = showinorder ? 1 : 0;
		
		validations = validations ? validations : '';
		
		var index = $('#wcfd_checkout_fields tbody tr').size();
		
		var newRow = '<tr class="row_'+index+'">';
		newRow += '<td width="1%" class="sort ui-sortable-handle">';
		newRow += '<input type="hidden" name="f_order['+index+']" class="f_order" value="'+index+'" />';
		newRow += '<input type="hidden" name="f_name['+index+']" class="f_name" value="" />';
		newRow += '<input type="hidden" name="f_name_new['+index+']" class="f_name_new" value="'+name+'" />';
		newRow += '<input type="hidden" name="f_type['+index+']" class="f_type" value="'+type+'" />';
		newRow += '<input type="hidden" name="f_label['+index+']" class="f_label" value="'+label+'" />';		
		newRow += '<input type="hidden" name="f_placeholder['+index+']" class="f_placeholder" value="'+placeholder+'" />';		
		newRow += '<input type="hidden" name="f_options['+index+']" class="f_options" value="'+optionsList+'" />';
		
		newRow += '<input type="hidden" name="f_class['+index+']" class="f_class" value="'+fieldClass+'" />';
		newRow += '<input type="hidden" name="f_label_class['+index+']" class="f_label_class" value="'+labelClass+'" />';
		
		newRow += '<input type="hidden" name="f_required['+index+']" class="f_required" value="'+required+'" />';
		newRow += '<input type="hidden" name="f_clear['+index+']" class="f_clear" value="'+clearRow+'" />';
		newRow += '<input type="hidden" name="f_enabled['+index+']" class="f_enabled" value="'+enabled+'" />';
		
		newRow += '<input type="hidden" name="f_show_in_email['+index+']" class="f_show_in_email" value="'+showinemail+'" />';
		newRow += '<input type="hidden" name="f_show_in_order['+index+']" class="f_show_in_order" value="'+showinorder+'" />';
				
		newRow += '<input type="hidden" name="f_validation['+index+']" class="f_validation" value="'+validations+'" />';
		newRow += '<input type="hidden" name="f_deleted['+index+']" class="f_deleted" value="0" />';
		newRow += '</td>';		
		newRow += '<td ><input type="checkbox" /></td>';		
		newRow += '<td class="name">'+name+'</td>';
		newRow += '<td class="id">'+type+'</td>';
		newRow += '<td>'+label+'</td>';
		newRow += '<td>'+placeholder+'</td>';
		newRow += '<td>'+validations+'</td>';
		if(required == true){
			newRow += '<td class="status"><span class="status-enabled tips">Yes</span></td>';
		}else{
			newRow += '<td class="status">-</td>';
		}
		if(clearRow == true){
			newRow += '<td class="status"><span class="status-enabled tips">Yes</span></td>';
		}else{
			newRow += '<td class="status">-</td>';
		}
		if(enabled == true){
			newRow += '<td class="status"><span class="status-enabled tips">Yes</span></td>';
		}else{
			newRow += '<td class="status">-</td>';
		}
		
		newRow += '<td><button type="button" onclick="openEditFieldForm(this)">Edit</button></td>';
		newRow += '</tr>';
		
		$('#wcfd_checkout_fields tbody tr:last').after(newRow);
		return true;
	}
				
	_openEditFieldForm = function openEditFieldForm(elm, rowId){
		var row = $(elm).closest('tr')
		
		var is_custom = row.find(".f_custom").val();
		var name  = row.find(".f_name").val();
		var type  = row.find(".f_type").val();
		var label = row.find(".f_label").val();
		var placeholder = row.find(".f_placeholder").val();
		var optionsList = row.find(".f_options").val();
		
		var field_classes = row.find(".f_class").val();
		var label_classes = row.find(".f_label_class").val();
		
		var required = row.find(".f_required").val();
		var clearRow = row.find(".f_clear").val();
		
		var enabled = row.find(".f_enabled").val();
		var validations = row.find(".f_validation").val();	
		
		var showinemail = row.find(".f_show_in_email").val();
		var showinorder = row.find(".f_show_in_order").val();
		
		is_custom = is_custom == 1 ? true : false;
		
		required = required == 1 ? true : false;
		clearRow = clearRow == 1 ? true : false;
		enabled  = enabled == 1 ? true : false;
		
		validations = validations.split(",");
		
		showinemail = showinemail == 1 ? true : false;
		showinorder = showinorder == 1 ? true : false;
		
		showinemail = is_custom == true ? showinemail : true;
		showinorder = is_custom == true ? showinorder : true;
								
		var form = $("#wcfd_edit_field_form_pp");
		form.find('.err_msgs').html('');
		form.find("input[name=rowId]").val(rowId);
		form.find("input[name=fname]").val(name);
		form.find("input[name=fnameNew]").val(name);
		form.find("select[name=ftype]").val(type);
		form.find("input[name=flabel]").val(label);
		form.find("input[name=fplaceholder]").val(placeholder);
		form.find("input[name=foptions]").val(optionsList);
		
		form.find("input[name=fclass]").val(field_classes);
		form.find("input[name=flabelclass]").val(label_classes);
		form.find("select[name=fvalidate]").val(validations).trigger("change");
		
		form.find("input[name=frequired]").prop('checked', required);
		form.find("input[name=fclearRow]").prop('checked', clearRow);
		form.find("input[name=fenabled]").prop('checked', enabled);		
		
		form.find("input[name=fshowinemail]").prop('checked', showinemail);	
		form.find("input[name=fshowinorder]").prop('checked', showinorder);	
				
		form.find("select[name=ftype]").change();
		$( "#wcfd_edit_field_form_pp" ).dialog( "open" );
		
		/*if(type == 'email' || type == 'tel' || type == 'country' || type == 'state'){
			
		}*/
				
		if(is_custom == false){			
			form.find("input[name=fnameNew]").prop('disabled', true);
			form.find("select[name=ftype]").prop('disabled', true);
			form.find("input[name=fshowinemail]").prop('disabled', true);
			form.find("input[name=fshowinorder]").prop('disabled', true);
			form.find("input[name=flabel]").focus();
		}else{
			form.find("input[name=fnameNew]").prop('disabled', false);
			form.find("select[name=ftype]").prop('disabled', false);
			form.find("input[name=fshowinemail]").prop('disabled', false);
			form.find("input[name=fshowinorder]").prop('disabled', false);
		}
	}
	
	function wcfd_update_row(form){
		var rowId = $(form).find("input[name=rowId]").val();
		
		var name  = $(form).find("input[name=fnameNew]").val();
		var type  = $(form).find("select[name=ftype]").val();
		var label = $(form).find("input[name=flabel]").val();
		var placeholder = $(form).find("input[name=fplaceholder]").val();
		var optionsList = $(form).find("input[name=foptions]").val();
		
		var fieldClass = $(form).find("input[name=fclass]").val();
		var labelClass = $(form).find("input[name=flabelclass]").val();
		
		var required = $(form).find("input[name=frequired]").prop('checked');
		var clearRow = $(form).find("input[name=fclearRow]").prop('checked');
		var enabled  = $(form).find("input[name=fenabled]").prop('checked');
		
		var showinemail = $(form).find("input[name=fshowinemail]").prop('checked');
		var showinorder = $(form).find("input[name=fshowinorder]").prop('checked');
		
		var validations = $(form).find("select[name=fvalidate]").val();
				
		var err_msgs = '';
		if(name == ''){
			err_msgs = 'Name is required';
		}else if(!isHtmlIdValid(name)){
			err_msgs = MSG_INVALID_NAME;
		}else if(type == ''){
			err_msgs = 'Type is required';
		}
		
		if(err_msgs != ''){
			$(form).find('.err_msgs').html(err_msgs);
			return false;
		}
		
		required = required ? 1 : 0;
		clearRow = clearRow ? 1 : 0;
		enabled  = enabled ? 1 : 0;
		
		showinemail = showinemail ? 1 : 0;
		showinorder = showinorder ? 1 : 0;
		
		validations = validations ? validations : '';
				
		var row = $('#wcfd_checkout_fields tbody').find('.row_'+rowId);
		row.find(".f_name").val(name);
		row.find(".f_type").val(type);
		row.find(".f_label").val(label);
		row.find(".f_placeholder").val(placeholder);
		row.find(".f_options").val(optionsList);
		
		row.find(".f_class").val(fieldClass);
		row.find(".f_label_class").val(labelClass);
		
		row.find(".f_required").val(required);
		row.find(".f_clear").val(clearRow);		
		row.find(".f_enabled").val(enabled);
		
		row.find(".f_show_in_email").val(showinemail);
		row.find(".f_show_in_order").val(showinorder);
		row.find(".f_validation").val(validations);	
		
		row.find(".td_name").html(name);
		row.find(".td_type").html(type);
		row.find(".td_label").html(label);
		row.find(".td_placeholder").html(placeholder);
		row.find(".td_validate").html(""+validations+"");
		row.find(".td_required").html(required == 1 ? '<span class="status-enabled tips">Yes</span>' : '-');
		row.find(".td_clear").html(clearRow == 1 ? '<span class="status-enabled tips">Yes</span>' : '-');
		row.find(".td_enabled").html(enabled == 1 ? '<span class="status-enabled tips">Yes</span>' : '-');
		return true;
	}
	
	_removeSelectedFields = function removeSelectedFields(){
		$('#wcfd_checkout_fields tbody tr').removeClass('strikeout');
		$('#wcfd_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			//$(this).closest('tr').remove();
			var row = $(this).closest('tr');
			if(!row.hasClass("strikeout")){
				row.addClass("strikeout");
			}
			row.find(".f_deleted").val(1);
			row.find(".f_edit_btn").prop('disabled', true);
			//row.find('.sort').removeClass('sort');
	  	});	
	}
	
	_enableDisableSelectedFields = function enableDisableSelectedFields(enabled){
		$('#wcfd_checkout_fields tbody input:checkbox[name=select_field]:checked').each(function () {
			var row = $(this).closest('tr');
			if(enabled == 0){
				if(!row.hasClass("thwcfd-disabled")){
					row.addClass("thwcfd-disabled");
				}
			}else{
				row.removeClass("thwcfd-disabled");				
			}
			
			row.find(".f_edit_btn").prop('disabled', enabled == 1 ? false : true);
			row.find(".td_enabled").html(enabled == 1 ? '<span class="status-enabled tips">Yes</span>' : '-');
			row.find(".f_enabled").val(enabled);
	  	});	
	}
	
	function wcfd_clear_form( form ){
		form.find('.err_msgs').html('');
		form.find("input[name=fname]").val('');
		form.find("input[name=fnameNew]").val('');
		form.find("select[name=ftype]").prop('selectedIndex',0);
		form.find("input[name=flabel]").val('');
		form.find("input[name=fplaceholder]").val('');
		form.find("input[name=foptions]").val('');
		
		form.find("input[name=fclass]").val('');
		form.find("input[name=flabelclass]").val('');
		form.find("select[name=fvalidate] option:selected").removeProp('selected');
		
		form.find("input[name=frequired]").prop('checked', true);
		form.find("input[name=fclearRow]").prop('checked', true);
		form.find("input[name=fenabled]").prop('checked', true);
		form.find("input[name=fshowinemail]").prop('checked', true);
		form.find("input[name=fshowinorder]").prop('checked', true);
	}
	
	function wcfd_prepare_field_order_indexes() {
		$('#wcfd_checkout_fields tbody tr').each(function(index, el){
			$('input.f_order', el).val( parseInt( $(el).index('#wcfd_checkout_fields tbody tr') ) );
		});
	};
	
	_fieldTypeChangeListner = function fieldTypeChangeListner(elm){
		var type = $(elm).val();
		var form = $(elm).closest('form');
		
		showAllFields(form);
		if(type === 'select'){			
			//form.find('.rowPlaceholder').hide();
			form.find('.rowValidate').hide();
		}else{			
			form.find('.rowOptions').hide();
		}			
	}
	
	function showAllFields(form){
		form.find('.rowOptions').show();
		form.find('.rowPlaceholder').show();
		form.find('.rowValidate').show();
	}
	
	_selectAllCheckoutFields = function selectAllCheckoutFields(elm){
		var checkAll = $(elm).prop('checked');
		$('#wcfd_checkout_fields tbody input:checkbox[name=select_field]').prop('checked', checkAll);
	}
	
	function isHtmlIdValid(id) {
		var re = /^[a-z\_]+[a-z0-9\_]*$/;
		return re.test(id.trim());
	}
	
	return {
		openNewFieldForm : _openNewFieldForm,
		openEditFieldForm : _openEditFieldForm,
		removeSelectedFields : _removeSelectedFields,
		enableDisableSelectedFields : _enableDisableSelectedFields,
		fieldTypeChangeListner : _fieldTypeChangeListner,
		selectAllCheckoutFields : _selectAllCheckoutFields,
   	};
}(window.jQuery, window, document));	

function openNewFieldForm(tabName){
	wcfd_settings.openNewFieldForm(tabName);		
}

function openEditFieldForm(elm, rowId){
	wcfd_settings.openEditFieldForm(elm, rowId);		
}
	
function removeSelectedFields(){
	wcfd_settings.removeSelectedFields();
}

function enableSelectedFields(){
	wcfd_settings.enableDisableSelectedFields(1);
}

function disableSelectedFields(){
	wcfd_settings.enableDisableSelectedFields(0);
}

function fieldTypeChangeListner(elm){	
	wcfd_settings.fieldTypeChangeListner(elm);
}
	
function thwcfdSelectAllCheckoutFields(elm){
	wcfd_settings.selectAllCheckoutFields(elm);
}