define([
    'jquery'
], function ($) {
    'use strict';
    return function (target) {
        $.validator.addMethod(
            'validate-no-zero',
            function (value) {
                return value != 0;
            },
            $.mage.__('0 is not allowed.')
        );

        return target;
    };
});
