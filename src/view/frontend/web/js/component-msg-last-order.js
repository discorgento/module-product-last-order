/**
 * Copyright © All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'jquery',
    'ko',
    'mage/url',
    'mage/translate',
    'mage/storage'
], function (Component, $, ko, urlBuilder, $t, storage) {
    'use strict';

    return Component.extend({
        isVisible: ko.observable(false),
        orderLink: ko.observable(''),
        orderDate: ko.observable(''),
        errorMessage: ko.observable(''),

        /**
         * Initializes the function.
         *
         * @param {Object} config - The configuration object.
         * @param {number} config.customerId - The ID of the customer.
         * @param {number} config.productId - The ID of the product.
         */
        initialize: function (config) {
            this._super();
            this.customerId = config.customerId;
            this.productId = config.productId;

            if (this.validateInput()) {
                this.checkCacheOrMakeRequest();
            }
        },

        /**
         * Validates input data.
         *
         * @returns {boolean} - True if valid, false otherwise.
         */
        validateInput: function () {
            if (!this.customerId || !this.productId) {
                this.errorMessage($t('Invalid customer or product ID.'));
                return false;
            }
            return true;
        },

        /**
         * Checks the cache before making a request.
         */
        checkCacheOrMakeRequest: function () {
            const cacheKey = `${this.customerId}-${this.productId}`;
            const cachedResponse = localStorage.getItem(cacheKey);
            if (cachedResponse) {
                this.handleResponse(JSON.parse(cachedResponse));
            } else {
                this.makeGraphQLRequest();
            }
        },

        /**
         * Makes a GraphQL request to check if a customer has purchased a product.
         */
        makeGraphQLRequest: function () {
            const urlGraphql = urlBuilder.build('graphql');
            const queryGraphql = `
                query CheckCustomerProductPurchase($customerId: String!, $productId: String!) {
                    hasCustomerPurchasedProduct(customerId: $customerId, productId: $productId) {
                        hasPurchased
                        orderLink
                        orderDate
                    }
                }`;

            const requestData = {
                query: queryGraphql,
                variables: {
                    customerId: this.customerId,
                    productId: this.productId
                }
            };

            storage.post(urlGraphql, JSON.stringify(requestData), true)
                .done(this.handleResponse.bind(this))
                .fail(this.handleError.bind(this));
        },

        /**
         * Handles the response from the GraphQL request.
         *
         * @param {Object} response - The response object.
         */
        handleResponse: function (response) {

            const { hasCustomerPurchasedProduct } = response.data || {};
            if (!hasCustomerPurchasedProduct) {
                this.errorMessage($t('No data found for this customer and product.'));
                return;
            }

            const { hasPurchased, orderLink, orderDate } = hasCustomerPurchasedProduct;

            this.isVisible(hasPurchased);
            this.orderLink(orderLink);
            this.orderDate(orderDate);
            this.errorMessage('');

            const cacheKey = `${this.customerId}-${this.productId}`;
            localStorage.setItem(cacheKey, JSON.stringify(response));
        },

        /**
         * Handles errors from the GraphQL request.
         *
         * @param {Object} error - The error object.
         */
        handleError: function (error) {
            console.error(error);
            this.errorMessage($t('An error occurred while processing your request.'));
        }
    });
});
