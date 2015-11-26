(function($){
	var $btnToAdd = $('#btn-to-add-process');
	var $ulProcesses = $('.elb-health-check__processes');

	$btnToAdd.on('click', function (){

		var $textToAdd = $('#text-to-add');
		if($textToAdd.val().trim() === ''){
			return;
		}

		var num = allocateNewIdNum();
		var html = '<li id="' + num + '" class="elb-health-check__process--to-display">';
		html += '<input type="text" name="process-to-add-' + num + '" value="' + $textToAdd.val() + '" />';
		html += '<a href="#" onClick="removeClickedParent(event)">削除</a>';
		html += '</li>';

		$ulProcesses.append(html);
		$textToAdd.val('');

	});

	function allocateNewIdNum(){
		var $liProcesses = $ulProcesses.find('li');

		var num = 0;
		for(var i = 0; i < $liProcesses.length; i ++){
			var id = $($liProcesses[i]).attr('id');
			if( +id > num ){
				num = +id;
			}
		}
		return num + 1;
	}

})(jQuery);

function removeClickedParent(event){
	(function($){

		var $aClicked = $(event.target);
		$aClicked.parent().remove();

	})(jQuery);
}
