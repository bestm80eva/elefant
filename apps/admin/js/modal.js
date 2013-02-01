$(function () {
	// n is the topmost dialog
	var n = 0;

	// create the html a new modal dialog
	function new_modal () {
		n++;
		$('body').append ('<div id="modal-overlay-' + n + '" class="modal-overlay"></div>' + 
			'<div id="modal-dialog-' + n + '" class="modal-dialog">' +
				'<div id="modal-titlebar-' + n + '" class="modal-titlebar">' +
					'<div class="modal-close-wrapper">' +
						'<a href="#" id="modal-close-' + n + '" data-modal="' + n + '" class="modal-close-button">X</a>' +
					'</div>' +
					'<div id="modal-title-' + n + '" class="modal-title"></div>' +
				'</div>' +
				'<div id="modal-content-' + n + '" class="modal-content"></div>' +
			'</div>');
		return n;
	}

	// removes the html from the specified dialog
	function close_modal (num) {
		$('#modal-dialog-' + num).remove ();
		$('#modal-overlay-' + num).remove ();
	}

	// centers the specified dialog
	function center_modal (num) {
		var modal = $('#modal-dialog-' + num),
			top = Math.max ($(window).height () - modal.outerHeight (), 0) / 2,
			left = Math.max ($(window).width () - modal.outerWidth (), 0) / 2;

		modal.css ({
			top: top + $(window).scrollTop () + ((num - 1) * 10),
			left: left + $(window).scrollLeft () + ((num - 1) * 10)
		});
	}

	// open a new modal dialog
	$.open_dialog = function (title, html, opts) {
		var defaults = {
			width: 550,
			height: 300
		};

		opts = opts || {};
		opts = $.extend (defaults, opts);

		var num = new_modal (),
			modal = $('#modal-dialog-' + n);

		$('#modal-title-' + num).html (title);
		$('#modal-content-' + num).html (html);
		$('#modal-overlay-' + num).show ().css ({'z-index': num * 1000});
		modal.show ().css ({'z-index': (num * 1000) + 1});

		if (opts.width) {
			modal.css ({width: opts.width + 'px'});
			modal.children ('.modal-close-wrapper').css ({width: (opts.width - 22) + 'px'});
		}

		if (opts.height) {
			modal.css ({height: opts.height + 'px'});
			modal.children ('.modal-content').css ({height: (opts.height - 98) + 'px'});
		}

		center_modal (num);

		$('#modal-close-' + num).click ($.close_dialog);

		return num;
	};

	// close the top or the specified dialog
	$.close_dialog = function (num) {
		if (typeof num === 'object') {
			num = $(num.target).data ('modal');
		} else {
			num = num ? num : n;
		}

		// cascade if outer dialog is closed
		for (var i = n; i >= num; i--) {
			close_modal (i);
		}

		// adjust active number
		n = num - 1;

		return false;
	}
});
