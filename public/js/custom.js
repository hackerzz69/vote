$(document).ready(function() {

	$('[data-toggle="tooltip"]').tooltip();

	$('#toTop').on('click',function (e) {
		e.preventDefault();

		var target = this.hash;
		var $target = $(target);

		$('html, body').stop().animate({
			'scrollTop': 0
		}, 900, 'swing');
	});

});