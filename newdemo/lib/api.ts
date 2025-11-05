// Mock API endpoints for GVEN Demo

const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

export interface QuotesResponse {
  fx_context: {
    fix: string;
    timestamp: string;
  };
  unit: string;
  currency: string;
  markets: Array<{
    market_id: string;
    market_name: string;
    local_currency: string;
    local_price: number;
    fx: number;
    price_usd: number;
    verified: boolean;
    updated: string;
  }>;
}

export interface KYCResponse {
  status: string;
  kyc_id: string;
  user_id: string;
}

export interface TokenizeResponse {
  status: string;
  token: string;
  brand: string;
  last4: string;
}

export interface ExecuteOrderResponse {
  status: string;
  order_id: string;
  filled_qty_oz: number;
  avg_price_usd: number;
  market_id: string;
  custody: string;
  broker: string;
  timestamp: string;
  settlement_ref: string;
}

export interface WalletResponse {
  base_currency: string;
  positions: Array<{
    position_id: string;
    asset: string;
    qty_oz: number;
    location: string;
    cost_basis_usd: number;
    mark_usd: number;
    pl_usd: number;
    pl_pct: number;
  }>;
}

export const api = {
  async getQuotes(currency: string = 'USD'): Promise<QuotesResponse> {
    await delay(500);
    return {
      fx_context: {
        fix: 'DEMO_SYNCHRONIZED',
        timestamp: '2025-10-30T16:00:00Z'
      },
      unit: 'troy_ounce',
      currency,
      markets: [
        {
          market_id: 'SHANGHAI',
          market_name: 'Shanghai',
          local_currency: 'CNY',
          local_price: 29150.12,
          fx: 0.0001367,
          price_usd: 3980.55,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'ZURICH',
          market_name: 'Zurich',
          local_currency: 'CHF',
          local_price: 3601.90,
          fx: 1.096,
          price_usd: 3947.28,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'MUMBAI',
          market_name: 'Mumbai',
          local_currency: 'INR',
          local_price: 333299.00,
          fx: 0.0120,
          price_usd: 3994.00,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'NEW_YORK',
          market_name: 'New York',
          local_currency: 'USD',
          local_price: 3999.10,
          fx: 1.0,
          price_usd: 3999.10,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'LONDON',
          market_name: 'London',
          local_currency: 'GBP',
          local_price: 3155.00,
          fx: 1.27,
          price_usd: 4006.85,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        }
      ]
    };
  },

  async submitKYC(data: { fullName: string; email: string; address: string }): Promise<KYCResponse> {
    await delay(600);
    return {
      status: 'approved',
      kyc_id: 'KYC-DEMO-123',
      user_id: 'USER-DEMO-001'
    };
  },

  async tokenizePayment(cardData: { number: string; name: string; expiry: string; cvv: string }): Promise<TokenizeResponse> {
    await delay(400);
    return {
      status: 'ok',
      token: 'tok_demo_abc123',
      brand: 'VISA',
      last4: cardData.number.slice(-4)
    };
  },

  async executeOrder(orderData: {
    qty_oz: number;
    market_id: string;
    price_usd: number;
    custody: string;
    payment_token: string;
  }): Promise<ExecuteOrderResponse> {
    await delay(800);
    const orderId = 'GVEN-DEMO-' + Date.now().toString(36).toUpperCase().slice(-4);
    return {
      status: 'filled',
      order_id: orderId,
      filled_qty_oz: orderData.qty_oz,
      avg_price_usd: orderData.price_usd,
      market_id: orderData.market_id,
      custody: orderData.custody,
      broker: 'DemoBroker IN',
      timestamp: new Date().toISOString(),
      settlement_ref: 'SETT-DEMO-' + Math.random().toString(36).substring(2, 8).toUpperCase()
    };
  },

  async getWallet(): Promise<WalletResponse> {
    await delay(300);
    
    // Try to get position from localStorage
    const storedPosition = localStorage.getItem('gven_position');
    if (storedPosition) {
      return JSON.parse(storedPosition);
    }
    
    // Empty wallet by default
    return {
      base_currency: 'USD',
      positions: []
    };
  },

  async getMark(asset: string = 'GOLD'): Promise<number> {
    await delay(200);
    // Return slightly varied mark price for demo
    const basePrice = 4012.20;
    const variation = (Math.random() - 0.5) * 10; // +/- $5
    return basePrice + variation;
  },

  getReceiptUrl(orderId: string): string {
    return `/static/demo/receipt-${orderId}.pdf`;
  }
};

// Helper to save position to localStorage
export const savePosition = (orderData: ExecuteOrderResponse, totalCost: number) => {
  const position: WalletResponse = {
    base_currency: 'USD',
    positions: [{
      position_id: 'POS-DEMO-1',
      asset: 'GOLD',
      qty_oz: orderData.filled_qty_oz,
      location: 'VAULT_MUMBAI',
      cost_basis_usd: totalCost,
      mark_usd: 4012.20,
      pl_usd: 4012.20 - totalCost,
      pl_pct: ((4012.20 - totalCost) / totalCost)
    }]
  };
  localStorage.setItem('gven_position', JSON.stringify(position));
  localStorage.setItem('gven_order', JSON.stringify(orderData));
};
