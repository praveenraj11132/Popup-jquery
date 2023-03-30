define(['jquery'], function($){
    "use strict";
    return function () {
        $(document).ready(function() {
            let popupBtn = $(".popup-btn");
            let popupContainer = $(".popup-container");
            let closeContainer = $(".close-container");
            let cancelButton = $("#cancel");

            popupBtn.click(function() {
                popupContainer.show();
            });

            closeContainer.click(function() {
                popupContainer.hide();
            });

            cancelButton.click(function() {
                popupContainer.hide();
            });
        });
    }
});
