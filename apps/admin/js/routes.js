function forEach(arr,func){ var i = -1; while(++i < arr.length) func.bind(arr[i])(i, arr); };

function icons(){
	return '<div class="actions">\
		<i class="fa fa-lg fa-times clickable" title="Remove" onclick="if(confirm(\'Do you want to remove this route?\')){this.parentNode.parentNode.remove();}"></i>\
		<i class="fa fa-lg fa-arrow-up clickable" title="Move Up" onclick="moveEntry(this.parentNode.parentNode, 1);"></i>\
		<i class="fa fa-lg fa-arrow-down clickable" title="Move Down" onclick="moveEntry(this.parentNode.parentNode, 0);"></i>\
	</div>';

}

function saveRoutes(){
	var out = {Alias:{},Disable:{},Redirect:{}};
	var a = document.querySelector('#routes-alias>ul'),
		d = document.querySelector('#routes-disable>ul'),
		r = document.querySelector('#routes-redirect>ul');
	forEach(a.children,function(){out.Alias[this.dataset['match']] = this.dataset['action'];});
	forEach(d.children,function(){out.Disable[this.dataset['match']] = this.dataset['action'];});
	forEach(r.children,function(){out.Redirect[this.dataset['match']] = this.dataset['action'];});
	
	$.post('/admin/api/routes',out).done(function(res){
		if (res.success) $.add_notice('Routes successfully saved.');
		else $.add_notice('Save unsuccessful: '+res.error);
	}).fail(function(res){ $.add_notice('A connection or server error occured. Try again.'); });
}

function moveEntry(node, which){
	if (which == 1 && node.previousElementSibling){ node.previousElementSibling.insertAdjacentElement('beforeBegin',node); }
	else if (which == 0 && node.nextElementSibling){ node.nextElementSibling.insertAdjacentElement('afterEnd',node); }
}

function newEntry(type){
	var html, title;
	if (type == 'alias') {
		title = 'New Alias Entry';
		html = '<label for="match">Alias match value:</label><br>\
			<input type="text" id="match" name="match" required autofocus><br>\
			<label for="action">Value to replace:</label><br>\
			<input type="text" id="action" name="action" required><br><br>\
			<input type="submit" value="Add" onclick="addAlias(this.parentNode.querySelector(\'#match\').value,this.parentNode.querySelector(\'#action\').value);return false;">';
	} else if (type == 'disable') {
		title = 'New Disabled Route';
		html = '<label for="match">Disable match value:</label><br>\
			<input type="text" id="match" name="match" required autofocus><br>\
			<label for="action">Match exact?</label>\
			<input type="checkbox" id="action" name="action"><br><br>\
			<input type="submit" value="Add" onclick="addDisable(this.parentNode.querySelector(\'#match\').value,this.parentNode.querySelector(\'#action\').checked);return false;">';
	} else if (type == 'redirect') {
		title = 'New Redirect Entry';
		html = '<label for="match">Redirect match value:</label><br>\
			<input type="text" id="match" name="match" required autofocus><br>\
			<label for="action">Destination resource:</label>\
			<input type="text" id="action" name="action" required><br><br>\
			<input type="submit" value="Add" onclick="addRedirect(this.parentNode.querySelector(\'#match\').value,this.parentNode.querySelector(\'#action\').value);return false;">';
	}
	$.open_dialog(title,html,{width:200,height:200});
}

function addAlias(match,action){
	match = '/'+ match.replace(/^\/|\/$/g,'');
	action = '/'+ action.replace(/^\/|\/$/g,'');
	if (document.querySelectorAll('#routes-alias li[data-match="'+ match +'"]').length){ alert('An entry for '+ match +' already exists.'); return; }
	var html = '<li data-match="'+ match +'" data-action="'+ action +'">'+ match +'<br>-> '+ action + icons() +'</li>';
	document.querySelector('#routes-alias>ul').insertAdjacentHTML('afterBegin',html);
	$.close_dialog();
}

function addDisable(match,action){
	match = '/'+ match.replace(/^\/|\/$/g,'');
	if (document.querySelectorAll('#routes-disable li[data-match="'+ match +'"]').length){ alert('An entry for '+ match +' already exists.'); return; }
	var html = '<li data-match="'+ match +'" data-action="'+ action +'">'+ match +'<br>-> '+ (action?'strict match':'loose match') + icons() +'</li>';
	document.querySelector('#routes-disable>ul').insertAdjacentHTML('afterBegin',html);
	$.close_dialog();
}

function addRedirect(match,action){
	match = '/'+ match.replace(/^\/|\/$/g,'');
	action = '/'+ action.replace(/^\/|\/$/g,'');
	if (document.querySelectorAll('#routes-redirect li[data-match="'+ match +'"]').length){ alert('An entry for '+ match +' already exists.'); return; }
	var html = '<li data-match="'+ match +'" data-action="'+ action +'">'+ match +'<br>-> '+ action + icons() +'</li>';
	document.querySelector('#routes-redirect>ul').insertAdjacentHTML('afterBegin',html);
	$.close_dialog();
}
