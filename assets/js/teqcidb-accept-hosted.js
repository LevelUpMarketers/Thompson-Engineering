/* global TEQCIDB_AUTHNET */
(function () {
  "use strict";

  // You will add the iframe markup elsewhere.
  // This script expects an iframe element with this id to exist on the page.
  var DEFAULT_IFRAME_ID = "teqcidb-authnet-accept-hosted-iframe";
  var DEFAULT_FORM_ID = "teqcidb-authnet-token-form";
  var DEFAULT_TOKEN_INPUT_ID = "teqcidb-authnet-token-input";

  // Authorize.net Accept Hosted base URLs
  // Sandbox: https://test.authorize.net/payment/payment
  // Production: https://accept.authorize.net/payment/payment
  function getAcceptHostedBaseUrl() {
    if (window.TEQCIDB_AUTHNET && TEQCIDB_AUTHNET.acceptHostedBaseUrl) {
      return TEQCIDB_AUTHNET.acceptHostedBaseUrl;
    }
    // Safe default is sandbox
    return "https://test.authorize.net/payment/payment";
  }

  function getRestTokenUrl() {
    if (!window.TEQCIDB_AUTHNET || !TEQCIDB_AUTHNET.restTokenUrl) {
      throw new Error("Missing TEQCIDB_AUTHNET.restTokenUrl");
    }
    return TEQCIDB_AUTHNET.restTokenUrl;
  }

  function getNonce() {
    // If your endpoint requires a nonce, pass it via wp_localize_script.
    return window.TEQCIDB_AUTHNET && TEQCIDB_AUTHNET.nonce ? TEQCIDB_AUTHNET.nonce : "";
  }

  function getLoadingMessage() {
    if (window.TEQCIDB_AUTHNET && TEQCIDB_AUTHNET.loadingMessage) {
      return TEQCIDB_AUTHNET.loadingMessage;
    }

    return "Loading secure payment form…";
  }

  function getErrorPrefix() {
    if (window.TEQCIDB_AUTHNET && TEQCIDB_AUTHNET.errorPrefix) {
      return TEQCIDB_AUTHNET.errorPrefix;
    }

    return "Unable to start payment:";
  }

  function getIframe(iframeId) {
    var id = iframeId || DEFAULT_IFRAME_ID;
    var el = document.getElementById(id);
    if (!el) {
      throw new Error("Accept Hosted iframe not found. Expected iframe id: " + id);
    }
    if (String(el.tagName).toLowerCase() !== "iframe") {
      throw new Error("Element is not an iframe. Id: " + id);
    }
    return el;
  }

  function getTokenForm() {
    var form = document.getElementById(DEFAULT_FORM_ID);
    if (!form || String(form.tagName).toLowerCase() !== "form") {
      throw new Error("Accept Hosted token form not found. Expected id: " + DEFAULT_FORM_ID);
    }

    var input = document.getElementById(DEFAULT_TOKEN_INPUT_ID);
    if (!input) {
      throw new Error("Accept Hosted token input not found. Expected id: " + DEFAULT_TOKEN_INPUT_ID);
    }

    return {
      form: form,
      input: input,
    };
  }

  function dispatch(name, detail) {
    try {
      window.dispatchEvent(new CustomEvent(name, { detail: detail || {} }));
    } catch (e) {
      // IE is not a concern here, but keep it safe
      var evt = document.createEvent("CustomEvent");
      evt.initCustomEvent(name, false, false, detail || {});
      window.dispatchEvent(evt);
    }
  }

  function safeJsonParse(maybeJson) {
    if (typeof maybeJson !== "string") return null;
    try {
      return JSON.parse(maybeJson);
    } catch (e) {
      return null;
    }
  }

  function isAllowedOrigin(origin) {
    // Authorize.net message origins typically come from these domains.
    // We also allow same-origin messages (your communicator page is same origin).
    var allowed = [
      window.location.origin,
      "https://test.authorize.net",
      "https://accept.authorize.net",
    ];
    return allowed.indexOf(origin) !== -1;
  }

  async function requestToken(payload) {
    var url = getRestTokenUrl();
    var nonce = getNonce();

    var res = await fetch(url, {
      method: "POST",
      headers: Object.assign(
        {
          "Content-Type": "application/json",
        },
        nonce ? { "X-WP-Nonce": nonce } : {}
      ),
      credentials: "same-origin",
      body: JSON.stringify(payload || {}),
    });

    var data = null;
    try {
      data = await res.json();
    } catch (e) {
      // fall through
    }

    if (!res.ok || !data || data.ok !== true || !data.token) {
      var msg =
        (data && (data.error || data.message)) ||
        ("Token request failed. HTTP " + res.status);
      throw new Error(msg);
    }

    return data.token;
  }

  function submitTokenToIframe(token, iframeId) {
    var tokenForm = getTokenForm();
    var iframe = getIframe(iframeId);

    tokenForm.input.value = token;
    tokenForm.form.action = getAcceptHostedBaseUrl();
    tokenForm.form.target = iframe.name || iframe.id;
    tokenForm.form.submit();
  }

  var state = {
    listening: false,
    currentIframeId: DEFAULT_IFRAME_ID,
  };

  function ensureListener() {
    if (state.listening) return;
    state.listening = true;

    window.addEventListener("message", function (event) {
      if (!event || !event.data) return;

      if (!isAllowedOrigin(event.origin)) {
        return;
      }

      // Accept Hosted often posts a JSON string with fields like:
      // { "action":"transactResponse", "response":{...} }
      // Or a related payload. We handle both JSON and non-JSON strings.
      var parsed = safeJsonParse(event.data);
      var payload = parsed || { raw: event.data };

      // Normalize common outcomes.
      // You can refine these once you see the exact payload in your environment.
      var action = payload.action || payload.eventType || payload.type || "";

      // Common pattern is action = "transactResponse"
      if (action === "transactResponse" || payload.response || payload.transactResponse) {
        var resp = payload.response || payload.transactResponse || payload;

        // Heuristic success detection:
        // Some payloads include a "responseCode" or "messages" structure.
        var responseCode =
          resp.responseCode ||
          (resp.transactionResponse && resp.transactionResponse.responseCode) ||
          "";

        // responseCode "1" is commonly approved in Authorize.net transaction responses.
        if (String(responseCode) === "1") {
          dispatch("teqcidb:authnet:success", payload);
          return;
        }

        // If we can detect cancellation
        if (payload.cancelled === true || action === "cancel" || action === "cancelled") {
          dispatch("teqcidb:authnet:cancelled", payload);
          return;
        }

        // Otherwise treat as error
        dispatch("teqcidb:authnet:error", payload);
        return;
      }

      // If we cannot classify it, still emit a generic message event
      dispatch("teqcidb:authnet:message", payload);
    });
  }

  async function startPayment(options) {
    options = options || {};

    ensureListener();

    var iframeId = options.iframeId || DEFAULT_IFRAME_ID;
    state.currentIframeId = iframeId;

    // Payload sent to your token endpoint.
    // Your PHP endpoint expects at least amount.
    // It already sets return and cancel URLs server-side if not provided.
    var tokenPayload = {
      amount: options.amount,
      invoiceNumber: options.invoiceNumber || "",
      description: options.description || "",
      customerEmail: options.customerEmail || "",
      customerId: options.customerId || "",
      // Optional overrides if you want them:
      returnUrl: options.returnUrl || "",
      cancelUrl: options.cancelUrl || "",
    };

    dispatch("teqcidb:authnet:loading", { iframeId: iframeId });

    var token = await requestToken(tokenPayload);

    submitTokenToIframe(token, iframeId);

    dispatch("teqcidb:authnet:loaded", { iframeId: iframeId, token: token });

    return token;
  }

  function getStatusElement(button) {
    if (!button || !button.parentNode) {
      return null;
    }

    var status = button.parentNode.querySelector(".teqcidb-register-pay-online-status");

    if (!status) {
      status = document.createElement("p");
      status.className = "teqcidb-register-pay-online-status";
      status.setAttribute("aria-live", "polite");
      button.insertAdjacentElement("afterend", status);
    }

    return status;
  }

  function setStatusMessage(button, message, isError) {
    var status = getStatusElement(button);

    if (!status) {
      return;
    }

    status.textContent = message || "";
    status.style.color = isError ? "#b32d2e" : "";
  }

  function getButtonOptions(button) {
    return {
      iframeId: button.getAttribute("data-teqcidb-iframe-id") || DEFAULT_IFRAME_ID,
      amount: button.getAttribute("data-teqcidb-amount") || "",
      invoiceNumber: button.getAttribute("data-teqcidb-invoice-number") || "",
      description: button.getAttribute("data-teqcidb-description") || "",
      customerEmail: button.getAttribute("data-teqcidb-customer-email") || "",
      customerId: button.getAttribute("data-teqcidb-customer-id") || "",
      returnUrl: button.getAttribute("data-teqcidb-return-url") || "",
      cancelUrl: button.getAttribute("data-teqcidb-cancel-url") || "",
    };
  }

  function bindRegisterButtons() {
    var buttons = document.querySelectorAll(".teqcidb-register-pay-online-button");

    if (!buttons.length) {
      return;
    }

    buttons.forEach(function (button) {
      button.addEventListener("click", async function () {
        var containerId = button.getAttribute("data-teqcidb-container-id");
        var container = containerId ? document.getElementById(containerId) : null;

        if (container) {
          container.hidden = false;
        }

        button.disabled = true;

        try {
          setStatusMessage(button, getLoadingMessage(), false);
          await startPayment(getButtonOptions(button));
          setStatusMessage(button, "", false);
        } catch (error) {
          var errorMessage = error && error.message ? error.message : "Payment initialization failed.";

          console.error("TEQCIDB Accept Hosted payment initialization failed.", errorMessage, error);
          setStatusMessage(button, getErrorPrefix() + " " + errorMessage, true);

          dispatch("teqcidb:authnet:error", {
            message: errorMessage,
          });
        } finally {
          button.disabled = false;
        }
      });
    });
  }

  window.TEQCIDB_AcceptHosted = {
    startPayment: startPayment,
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", bindRegisterButtons);
  } else {
    bindRegisterButtons();
  }
})();
