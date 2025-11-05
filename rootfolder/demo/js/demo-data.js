(function (global) {
  const DELAY_MIN = 400;
  const DELAY_MAX = 800;

  function delay() {
    const ms = Math.floor(Math.random() * (DELAY_MAX - DELAY_MIN + 1)) + DELAY_MIN;
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  function deepClone(value) {
    return JSON.parse(JSON.stringify(value));
  }

  const quotesPayload = {
    fx_context: { fix: "DEMO_SYNCHRONIZED", timestamp: "2025-10-30T16:00:00Z" },
    unit: "troy_ounce",
    currency: "USD",
    markets: [
      {
        market_id: "MUMBAI",
        market_name: "Mumbai",
        local_currency: "INR",
        local_price: 333299.0,
        fx: 0.012,
        price_usd: 3994.0,
        verified: true,
        updated: "2025-10-30T16:00:00Z",
      },
      {
        market_id: "NEW_YORK",
        market_name: "New York",
        local_currency: "USD",
        local_price: 4005.1,
        fx: 1.0,
        price_usd: 4005.1,
        verified: true,
        updated: "2025-10-30T16:00:00Z",
      },
      {
        market_id: "ZURICH",
        market_name: "Zurich",
        local_currency: "CHF",
        local_price: 3660.5,
        fx: 1.1,
        price_usd: 4026.55,
        verified: true,
        updated: "2025-10-30T16:00:00Z",
      },
      {
        market_id: "SHANGHAI",
        market_name: "Shanghai",
        local_currency: "CNY",
        local_price: 29250.12,
        fx: 0.0001367,
        price_usd: 3999.0,
        verified: true,
        updated: "2025-10-30T16:00:00Z",
      },
      {
        market_id: "LONDON",
        market_name: "London",
        local_currency: "GBP",
        local_price: 3155.0,
        fx: 1.27,
        price_usd: 4006.85,
        verified: true,
        updated: "2025-10-30T16:00:00Z",
      },
    ],
  };

  const walletPayload = {
    base_currency: "USD",
    positions: [
      {
        position_id: "POS-DEMO-1",
        asset: "GOLD",
        qty_oz: 1.0,
        location: "VAULT_MUMBAI",
        cost_basis_usd: 3994.0,
        mark_usd: 4012.2,
        pl_usd: 18.2,
        pl_pct: 0.0046,
      },
    ],
  };

  const demoStateKey = "gven-demo-state";

  function readState() {
    try {
      const stored = localStorage.getItem(demoStateKey);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch (error) {
      console.warn("Failed to parse demo state", error);
    }
    return {
      kyc: null,
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
      payment: null,
      execution: null,
    };
  }

  function writeState(nextState) {
    localStorage.setItem(demoStateKey, JSON.stringify(nextState));
  }

  function updateState(patch) {
    const current = readState();
    const next = { ...current, ...patch };
    writeState(next);
    return next;
  }

  function resetState() {
    localStorage.removeItem(demoStateKey);
  }

  const DemoAPI = {
    async getQuotes() {
      await delay();
      return deepClone(quotesPayload);
    },
    async submitKyc(payload) {
      await delay();
      updateState({ kyc: { ...payload, status: "approved", kyc_id: "KYC-DEMO-123" } });
      return { status: "approved", kyc_id: "KYC-DEMO-123", user_id: "USER-DEMO-001" };
    },
    async tokenizePayment(cardPayload) {
      await delay();
      updateState({ payment: { ...cardPayload, token: "tok_demo_abc123", brand: "VISA", last4: "4242" } });
      return { status: "ok", token: "tok_demo_abc123", brand: "VISA", last4: "4242" };
    },
    async executeOrder(orderPayload) {
      await delay();
      const execution = {
        status: "filled",
        order_id: "GVEN-DEMO-0001",
        filled_qty_oz: 1.0,
        avg_price_usd: 3994.0,
        market_id: "MUMBAI",
        custody: "VAULT_MUMBAI",
        broker: "DemoBroker IN",
        timestamp: "2025-10-30T16:00:42Z",
        settlement_ref: "SETT-DEMO-89X",
      };
      updateState({ execution, order: { ...readState().order, confirmed: true } });
      localStorage.setItem("gven-demo-wallet", JSON.stringify(walletPayload));
      return deepClone(execution);
    },
    async getWallet() {
      await delay();
      const stored = localStorage.getItem("gven-demo-wallet");
      if (stored) {
        return JSON.parse(stored);
      }
      return deepClone(walletPayload);
    },
    async getReceipt() {
      await delay();
      return { pdf_url: "../static/demo/receipt-GVEN-DEMO-0001.pdf" };
    },
    readState,
    writeState,
    updateState,
    resetState,
  };

  global.DemoAPI = DemoAPI;
})(window);
