/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'jquery',
    'ko',
    'mage/url',
    'mage/translate'
], function (Component, $, ko, urlBuilder, $t) {
    'use strict';

    return Component.extend({
        isVisible: ko.observable(false),
        orderLink: ko.observable(''),
        orderDate: ko.observable(''),

        /**
         * Initializes the function.
         *
         * @param {Object} config - The configuration object.
         * @param {number} config.customerId - The ID of the customer.
         * @param {number} config.productId - The ID of the product.
         */
        initialize: function (config) {
            const { customerId, productId } = config;

            this._super();
            this.makeGraphQLRequest();
        },

        /**
         * Makes a GraphQL request to check if a customer has purchased a product.
         */
        makeGraphQLRequest: function () {
            let self = this;

            let urlGraphql = urlBuilder.build('graphql');
            let queryGraphqlObject =  {
                query: `
                query CheckCustomerProductPurchase($customerId: String!, $productId: String!) {
                    hasCustomerPurchasedProduct(customerId: $customerId, productId: $productId) {
                        hasPurchased
                        orderLink
                        orderDate
                    }
                }`
            };

            let variablesGraphql = JSON.stringify({
                customerId: self.customerId,
                productId: self.productId
            });

            let queryEncoded = $.param(queryGraphqlObject);
            let queryGraphql = queryEncoded+'&variables='+ variablesGraphql;
            let requestConfig = {
                url: urlGraphql +'?'+queryGraphql,
                method: 'GET'
            };

            $.ajax(requestConfig).done(function (response) {
                const { hasCustomerPurchasedProduct } = response.data || {};
                const { hasPurchased, orderLink, orderDate } = hasCustomerPurchasedProduct || {};

                if (hasPurchased) {
                    self.isVisible(true);
                }

                self.orderLink(orderLink);
                self.orderDate(orderDate);
            }).fail(function (error) {
                console.log(error);
            });
        }
    });
});
