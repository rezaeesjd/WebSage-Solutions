// Mock data for GVEN Demo - aligned with API spec

export interface Market {
  market_id: string;
  market_name: string;
  local_currency: string;
  local_price: number;
  fx: number;
  price_usd: number;
  verified: boolean;
  updated: string;
}

export interface Asset {
  id: string;
  name: string;
  unit: string;
  enabled: boolean;
}

export const assets: Asset[] = [
  { id: 'GOLD', name: 'Gold', unit: 'oz', enabled: true },
  { id: 'SILVER', name: 'Silver', unit: 'oz', enabled: false },
  { id: 'PLATINUM', name: 'Platinum', unit: 'oz', enabled: false },
  { id: 'PALLADIUM', name: 'Palladium', unit: 'oz', enabled: false }
];

export interface Currency {
  id: string;
  name: string;
  symbol: string;
  enabled: boolean;
}

export const currencies: Currency[] = [
  { id: 'USD', name: 'US Dollar', symbol: '$', enabled: true },
  { id: 'EUR', name: 'Euro', symbol: '€', enabled: false },
  { id: 'GBP', name: 'British Pound', symbol: '£', enabled: false },
  { id: 'INR', name: 'Indian Rupee', symbol: '₹', enabled: false },
  { id: 'CNY', name: 'Chinese Yuan', symbol: '¥', enabled: false }
];

export const calculateFees = (basePrice: number) => {
  const flatFee = 9.00;
  const spreadFee = basePrice * 0.001; // 0.1%
  return {
    broker: flatFee,
    spread: spreadFee,
    total: flatFee + spreadFee
  };
};

export interface UserKYC {
  fullName: string;
  email: string;
  address: string;
  country: string;
  dob?: string;
  verified: boolean;
}
