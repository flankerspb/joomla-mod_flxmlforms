$(document).ready(function(){
	$('form.fl-formajax').submit(function(event)
	{
		event.preventDefault();
		
		var form = $(this);
		var mid = form.attr('name');
		var overlay = form.parent().children('.fl-form-loading');
		
		var data = $(this).serializeArray();
		data.push({'name': 'module', 'value': 'flxmlforms'});
		data.push({'name': 'option', 'value': 'com_ajax'});
		data.push({'name': 'format', 'value': 'json'});
		data.push({'name': 'mid', 'value': mid});
		
		//AJAX request
		$.ajax({
			type: 'post',
			data: data,
			beforeSend: function(){overlay.attr('hidden',false);},
			complete: function(){overlay.attr('hidden',true);}
		})
			.done(function(response)
			{
				console.log(response);
				//success
				if(response.success)
				{
					form[0].reset();
					
					//OK
					if(response.data)
					{
						Joomla.renderMessages({'success': [response.data]});
					}
					//WARNING
					else
					{
						Joomla.renderMessages({'warning': [Joomla.JText._('MOD_FLXMLFORMS_MESSAGE_WARNING')]});
					}
				}
				else
				{
					//ERROR
					if(response.message)
					{
						Joomla.renderMessages({'error': [response.message]});
					}
					//UNKNONE ERROR
					else
					{
						Joomla.renderMessages({'error': [Joomla.JText._('MOD_FLXMLFORMS_MESSAGE_ERROR_UNKNOWN')]});
					}
				}
			})
			//PROCESSING REQUEST ERROR
			.fail(function(response)
			{
				Joomla.renderMessages({'error': [Joomla.JText._('MOD_FLXMLFORMS_MESSAGE_ERROR_FAIL')]});
			});
	});
});
