/**
 * webtrees: online genealogy
 * Copyright (C) 2015 Łukasz Wileński
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

jQuery('#ancestors_table-pdf').click(function(){
	jQuery('#dialog-confirm').dialog({
		resizable:false,
		width:300,
		modal:true,
		open: function() {
			// Close the window when we click outside it.
			var self = this;
			jQuery('.ui-widget-overlay').on('click', function () {
				jQuery(self).dialog('close');
			});
		}
	});
});
jQuery('select').change(function(){
	var select = jQuery('select option:selected').val();
	var gen = select - 1;
	jQuery('#dialog-confirm').dialog('close');
	getPDFDirect(gen, root);
});
function getPDFDirect(data, root) {
	var PageTitle = 'Ancestors';
	if ((data + 1) == 5) {
		var format = 'A4';
		var gen = 5;
	} else {
		var format = 'A3';
		var gen = data ;
	}
	jQuery.ajax({
		type:'GET',
		url:'module.php?mod=' + ModuleName + '&mod_action=pdf_direct&format=' + format + '&gen=' +gen + '&root=' + root,
		csrf:WT_CSRF_TOKEN,
		dataType:'html',
		success:function () {
			window.location.href = 'module.php?mod=' + ModuleName + '&mod_action=show_pdf&title=' + PageTitle + '&format=' + format;
		}
	});
}