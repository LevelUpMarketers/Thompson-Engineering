/* global TEQCIDB_AUTHNET */
(function () {
  "use strict";

  // You will add the iframe markup elsewhere.
  // This script expects an iframe element with this id to exist on the page.
  var DEFAULT_IFRAME_ID = "teqcidb-authnet-accept-hosted-iframe";

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

  function buildIframeSrc(token) {
    var base = getAcceptHostedBaseUrl();
    // Authorize.net Accept Hosted expects token query param
    return base + "?token=" + encodeURIComponent(token);
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

    var iframe = getIframe(iframeId);
    iframe.src = buildIframeSrc(token);

    dispatch("teqcidb:authnet:loaded", { iframeId: iframeId, token: token });

    return token;
  }

  window.TEQCIDB_AcceptHosted = {
    startPayment: startPayment,
  };
})();
