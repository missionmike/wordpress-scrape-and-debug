/* global jQuery */
if (typeof jQuery !== "undefined") {
    (function($) {
        $(document).ready(function() {
        	$(".dts_settings_debuggers input[type=\"checkbox\"]").on("change", function(e) {
        		var val = $(this).is(":checked");
        		if (val === true) {
        			$(this).parent(".dts_settings_debuggers").removeClass("unchecked");
        		} else {
        			$(this).parent(".dts_settings_debuggers").addClass("unchecked");
        		}
        	});

            $(".debug-btn-title").each(function() {
                var $next = $(this).next();
                if ($next.length === 0 || $next.hasClass("debug-btn-title")) {
                    $(this).hide();
                }
            });
        });
    })(jQuery);
}
