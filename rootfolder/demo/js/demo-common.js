(function (global) {
  function formatCurrency(value, currency = "USD") {
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency,
      minimumFractionDigits: 2,
    }).format(value);
  }

  function formatPercent(value) {
    return `${(value * 100).toFixed(2)}%`;
  }

  function applyDemoGuard(root = document) {
    root.querySelectorAll('[data-demo-disabled="true"]').forEach((el) => {
      if (el.dataset.demoGuarded === "true") {
        return;
      }
      el.classList.add("demo-disabled");
      el.setAttribute("aria-disabled", "true");
      el.setAttribute("tabindex", "-1");
      if ("disabled" in el) {
        el.setAttribute("disabled", "true");
      }
      el.addEventListener(
        "click",
        (event) => {
          event.preventDefault();
          event.stopPropagation();
        },
        { capture: true }
      );
      el.dataset.demoGuarded = "true";
    });
  }

  function hydrateHeader() {
    const ribbon = document.getElementById("demoRibbon");
    if (ribbon) {
      ribbon.textContent = "Demo Mode — follow the highlighted steps to complete a simulated purchase.";
    }
  }

  function formatDateTime(value) {
    try {
      const date = new Date(value);
      return date.toLocaleString("en-US", { timeZone: "UTC" }) + " UTC";
    } catch (error) {
      return value;
    }
  }

  function ensureStateDefaults() {
    const state = global.DemoAPI.readState();
    if (!state.order) {
      global.DemoAPI.updateState({
        order: {
          asset: "GOLD",
          market: "MUMBAI",
          currency: "USD",
          quantityOz: 1,
          custody: "VAULT_MUMBAI",
          paymentMethod: "CARD",
          unitPriceUsd: 3994.0,
          feesUsd: 9.0,
          spreadPct: 0.001,
          fxRate: 0.012,
        },
      });
    }
    return global.DemoAPI.readState();
  }

  async function openReceipt() {
    const button = document.getElementById("btnDownloadReceipt");
    if (button) {
      button.disabled = true;
      button.textContent = "Preparing receipt…";
    }
    const { pdf_url } = await global.DemoAPI.getReceipt();
    if (button) {
      button.disabled = false;
      button.textContent = "Download Receipt";
    }
    window.open(pdf_url, "_blank");
  }

  global.DemoHelpers = {
    formatCurrency,
    formatPercent,
    applyDemoGuard,
    hydrateHeader,
    formatDateTime,
    ensureStateDefaults,
    openReceipt,
  };
})(window);
