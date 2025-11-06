// Mock API endpoints for GVEN Demo

const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));

export interface MarketHistoryPoint {
  timestamp: string;
  prices: Record<string, number>;
}

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
  history: MarketHistoryPoint[];
}

export type Market = QuotesResponse['markets'][number];

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
  market_name: string;
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
    market_name: string;
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
          market_id: 'MILAN',
          market_name: 'Milan',
          local_currency: 'EUR',
          local_price: 3658.74,
          fx: 1.078,
          price_usd: 3941.21,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'ZURICH',
          market_name: 'Zurich',
          local_currency: 'CHF',
          local_price: 3608.4,
          fx: 1.095,
          price_usd: 3950.18,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'LONDON',
          market_name: 'London',
          local_currency: 'GBP',
          local_price: 3126.8,
          fx: 1.27,
          price_usd: 3970.04,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'NEW_YORK',
          market_name: 'New York',
          local_currency: 'USD',
          local_price: 3989.65,
          fx: 1.0,
          price_usd: 3989.65,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'DUBAI',
          market_name: 'Dubai',
          local_currency: 'AED',
          local_price: 14662.4,
          fx: 0.2724,
          price_usd: 3994.39,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        },
        {
          market_id: 'SHANGHAI',
          market_name: 'Shanghai',
          local_currency: 'CNY',
          local_price: 29195.2,
          fx: 0.0001378,
          price_usd: 4024.9,
          verified: true,
          updated: '2025-10-30T16:00:00Z'
        }
      ],
      history: [
        {
          timestamp: '2025-10-24T16:00:00Z',
          prices: {
            MILAN: 3924.1,
            ZURICH: 3932.6,
            LONDON: 3950.3,
            NEW_YORK: 3972.8,
            DUBAI: 3978.5,
            SHANGHAI: 4002.7
          }
        },
        {
          timestamp: '2025-10-25T16:00:00Z',
          prices: {
            MILAN: 3928.4,
            ZURICH: 3935.1,
            LONDON: 3954.6,
            NEW_YORK: 3978.2,
            DUBAI: 3982.1,
            SHANGHAI: 4005.3
          }
        },
        {
          timestamp: '2025-10-26T16:00:00Z',
          prices: {
            MILAN: 3932.9,
            ZURICH: 3941.3,
            LONDON: 3959.8,
            NEW_YORK: 3981.6,
            DUBAI: 3986.4,
            SHANGHAI: 4009.8
          }
        },
        {
          timestamp: '2025-10-27T16:00:00Z',
          prices: {
            MILAN: 3936.5,
            ZURICH: 3945.7,
            LONDON: 3962.1,
            NEW_YORK: 3984.2,
            DUBAI: 3988.9,
            SHANGHAI: 4015.1
          }
        },
        {
          timestamp: '2025-10-28T16:00:00Z',
          prices: {
            MILAN: 3940.3,
            ZURICH: 3948.9,
            LONDON: 3964.7,
            NEW_YORK: 3986.7,
            DUBAI: 3991.6,
            SHANGHAI: 4018.4
          }
        },
        {
          timestamp: '2025-10-29T16:00:00Z',
          prices: {
            MILAN: 3941.21,
            ZURICH: 3950.18,
            LONDON: 3970.04,
            NEW_YORK: 3989.65,
            DUBAI: 3994.39,
            SHANGHAI: 4024.9
          }
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
    market_name: string;
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
      market_name: orderData.market_name,
      custody: orderData.custody,
      broker: 'DemoBroker IT',
      timestamp: new Date().toISOString(),
      settlement_ref: 'SETT-DEMO-' + Math.random().toString(36).substring(2, 8).toUpperCase()
    };
  },

  async getWallet(): Promise<WalletResponse> {
    await delay(300);

    // Try to get position from localStorage
    const storedPosition = localStorage.getItem('gven_position');
    if (storedPosition) {
      try {
        const parsed = JSON.parse(storedPosition) as WalletResponse;
        if (Array.isArray(parsed.positions)) {
          parsed.positions = parsed.positions.map((position) => ({
            ...position,
            market_name:
              position.market_name ||
              position.location
                .replace('VAULT_', '')
                .split('_')
                .map((part) => part.charAt(0) + part.slice(1).toLowerCase())
                .join(' '),
          }));
        }
        return parsed;
      } catch (error) {
        console.error('Failed to parse stored wallet data', error);
      }
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
      location: orderData.custody,
      market_name: orderData.market_name,
      cost_basis_usd: totalCost,
      mark_usd: 4012.20,
      pl_usd: 4012.20 - totalCost,
      pl_pct: ((4012.20 - totalCost) / totalCost)
    }]
  };
  localStorage.setItem('gven_position', JSON.stringify(position));
  localStorage.setItem('gven_order', JSON.stringify(orderData));
};
