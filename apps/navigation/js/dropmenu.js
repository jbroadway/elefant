$(function () {
	$('.dropmenu ul').css ({display: 'none'}); // opera fix
	$('.dropmenu li').hover (
		function () {
			$(this).find ('ul:first').css ({visibility: 'visible', display: 'none'}).slideDown (100);
		},
		function () {
			$(this).find ('ul:first').css ({visibility: 'hidden'});
		}
	);
});
