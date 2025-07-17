<?php
// This file holds all the settings related to API.
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//Define variables that holds API settings and urls.
//Get the user credential for the account and change the user credentials
define("USERNAME", "jevans@highlevelmarketing.com");
define("PASSWORD", "America123"); //pvc
define("GRANT_TYPE","password");


define("BASE_URL","https://api.paytrace.com"); //Production

//API version
define("API_VERSION", "/v1");

// Url for OAuth Token
define("URL_OAUTH",BASE_URL."/oauth/token");

// Url for OAuth Token
define("URL_PROTECTAUTH",BASE_URL.API_VERSION."/payment_fields/token/create");

// URL for Keyed Sale
define("URL_PROTECT_SALE",BASE_URL.API_VERSION."/transactions/sale/pt_protect");

// URL for Keyed Authorization
define("URL_PROTECT_AUTHORIZATION" ,BASE_URL.API_VERSION."/transactions/authorization/pt_protect");

// URL for Capture Transaction
define("URL_CAPTURE", BASE_URL.API_VERSION."/transactions/authorization/capture");

// URL for Create Customer(PayTrace Vault) Method
define("URL_PROTECT_CREATE_CUSTOMER", BASE_URL.API_VERSION."/customer/pt_protect_create");

// URL for Create Customer(PayTrace Vault) Method
define("URL_PROTECT_UPDATE_CUSTOMER", BASE_URL.API_VERSION."/customer/pt_protect_update");

// URL for Create Customer(PayTrace Vault) Method
define("URL_PROTECT_SALE_CREATE_CUSTOMER", BASE_URL.API_VERSION."/transactions/sale/pt_protect_customer");
