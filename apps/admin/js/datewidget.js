$(function () {
	$('.datewidget-date').datepicker ({
		ampm: true,
		dateFormat: 'yy-mm-dd'
	});
	$('.datewidget-datetime').datetimepicker ({
		timeFormat: 'hh:mm:ss',
		dateFormat: 'yy-mm-dd',
		hourGrid: 4,
		minuteGrid: 10
	});
});