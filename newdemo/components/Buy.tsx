import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { ShoppingCart, Vault, Package, CreditCard, Building, Wallet as WalletIcon } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Badge } from './ui/badge';
import { Label } from './ui/label';
import { RadioGroup, RadioGroupItem } from './ui/radio-group';
import { DisabledOverlay } from './DisabledOverlay';
import { calculateFees } from '../lib/mockData';
import type { Market } from '../lib/api';

export function Buy() {
  const navigate = useNavigate();
  const [market, setMarket] = useState<Market | null>(null);
  const [timeLeft, setTimeLeft] = useState(30); // 30 seconds demo lock
  
  const [quantity] = useState(1);
  const [custody, setCustody] = useState<'vault' | 'delivery' | null>('vault');
  const [payment, setPayment] = useState<'card' | 'bank' | 'wallet' | null>('card');

  useEffect(() => {
    // Get selected market from sessionStorage
    const storedMarket = sessionStorage.getItem('selectedMarket');
    if (storedMarket) {
      setMarket(JSON.parse(storedMarket));
    }
  }, []);

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft((prev) => (prev > 0 ? prev - 1 : 30)); // Reset to 30 when it hits 0
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  if (!market) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
        <p className="text-slate-600">Loading...</p>
      </div>
    );
  }

  const basePrice = market.price_usd * quantity;
  const fees = calculateFees(basePrice);
  const total = basePrice + fees.total;

  const handleContinue = () => {
    if (custody && payment) {
      // Store order config in sessionStorage
      sessionStorage.setItem('orderConfig', JSON.stringify({
        market,
        quantity,
        custody,
        payment,
        basePrice,
        fees,
        total
      }));
      navigate('/kyc?next=/pay');
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="max-w-5xl mx-auto px-4 py-12">
        <div className="mb-8">
          <div className="flex items-center gap-2 mb-2">
            <ShoppingCart className="w-6 h-6 text-blue-600" />
            <h1 id="buyHeader" className="text-slate-900">Buy Gold — {market.market_name} Market</h1>
          </div>
          <p className="text-slate-600">
            Asset: Gold (1 oz) • Currency: USD
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left Column - Configuration */}
          <div className="lg:col-span-2 space-y-6">
            {/* Quantity */}
            <Card>
              <CardHeader>
                <CardTitle>Quantity</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-4">
                  <div className="flex-1">
                    <Label htmlFor="quantityInput">Amount (oz)</Label>
                    <div id="quantityInput" className="mt-2 p-4 bg-green-50 border-2 border-green-500 rounded-md">
                      <span className="text-slate-900">1.000 oz</span>
                      <p className="text-sm text-slate-600 mt-1">
                        Demo limited to 1 oz purchase (min 1, max disabled)
                      </p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Custody */}
            <Card>
              <CardHeader>
                <CardTitle>Custody Option</CardTitle>
              </CardHeader>
              <CardContent>
                <RadioGroup id="custodySelector" value={custody || ''} onValueChange={(v) => setCustody(v as 'vault' | 'delivery')}>
                  <div className="space-y-3">
                    <div className="flex items-center space-x-3 p-4 border-2 border-green-500 rounded-lg bg-green-50">
                      <RadioGroupItem value="vault" id="vault" />
                      <Label htmlFor="vault" className="flex-1 cursor-pointer">
                        <div className="flex items-center gap-2 mb-1">
                          <Vault className="w-4 h-4" />
                          <span>Vault Storage ({market.market_name})</span>
                          <Badge className="bg-green-600 text-white">Recommended</Badge>
                        </div>
                        <p className="text-sm text-slate-600">
                          Secure storage in {market.market_name}. Low annual fee.
                        </p>
                      </Label>
                    </div>
                    
                    <DisabledOverlay disabled={true}>
                      <div className="flex items-center space-x-3 p-4 border rounded-lg">
                        <RadioGroupItem value="delivery" id="delivery" disabled />
                        <Label htmlFor="delivery" className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <Package className="w-4 h-4" />
                            <span>Physical Delivery</span>
                          </div>
                          <p className="text-sm text-slate-600">
                            Shipped to your address. Additional fees apply.
                          </p>
                        </Label>
                      </div>
                    </DisabledOverlay>
                  </div>
                </RadioGroup>
              </CardContent>
            </Card>

            {/* Payment Method */}
            <Card>
              <CardHeader>
                <CardTitle>Payment Method</CardTitle>
              </CardHeader>
              <CardContent>
                <RadioGroup id="paymentMethodSelector" value={payment || ''} onValueChange={(v) => setPayment(v as 'card' | 'bank' | 'wallet')}>
                  <div className="space-y-3">
                    <div className="flex items-center space-x-3 p-4 border-2 border-green-500 rounded-lg bg-green-50">
                      <RadioGroupItem value="card" id="card" />
                      <Label htmlFor="card" className="flex-1 cursor-pointer">
                        <div className="flex items-center gap-2 mb-1">
                          <CreditCard className="w-4 h-4" />
                          <span>Credit/Debit Card</span>
                          <Badge className="bg-green-600 text-white">Instant</Badge>
                        </div>
                        <p className="text-sm text-slate-600">
                          Process immediately. 3D Secure protected.
                        </p>
                      </Label>
                    </div>
                    
                    <DisabledOverlay disabled={true}>
                      <div className="flex items-center space-x-3 p-4 border rounded-lg">
                        <RadioGroupItem value="bank" id="bank" disabled />
                        <Label htmlFor="bank" className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <Building className="w-4 h-4" />
                            <span>Bank Transfer</span>
                          </div>
                          <p className="text-sm text-slate-600">
                            1-3 business days. Lower fees.
                          </p>
                        </Label>
                      </div>
                    </DisabledOverlay>

                    <DisabledOverlay disabled={true}>
                      <div className="flex items-center space-x-3 p-4 border rounded-lg">
                        <RadioGroupItem value="wallet" id="wallet" disabled />
                        <Label htmlFor="wallet" className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <WalletIcon className="w-4 h-4" />
                            <span>GVEN Wallet</span>
                          </div>
                          <p className="text-sm text-slate-600">
                            Pay from your GVEN balance.
                          </p>
                        </Label>
                      </div>
                    </DisabledOverlay>
                  </div>
                </RadioGroup>
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Summary */}
          <div className="lg:col-span-1">
            <Card id="priceBreakdown" className="sticky top-4">
              <CardHeader>
                <CardTitle>Order Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Unit Price (USD)</span>
                    <span className="text-slate-900">${market.price_usd.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">FX Rate Used</span>
                    <span className="text-slate-900">{market.fx}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Quantity</span>
                    <span className="text-slate-900">{quantity} oz</span>
                  </div>
                  <div className="border-t pt-2">
                    <div className="flex justify-between text-sm">
                      <span className="text-slate-600">Subtotal</span>
                      <span className="text-slate-900">${basePrice.toFixed(2)}</span>
                    </div>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Estimated Fees</span>
                    <span className="text-slate-900">${fees.total.toFixed(2)}</span>
                  </div>
                  <div className="border-t pt-2 mt-2">
                    <div className="flex justify-between">
                      <span className="text-slate-900">Estimated Total (USD)</span>
                      <span className="text-slate-900">${total.toFixed(2)}</span>
                    </div>
                  </div>
                </div>

                <div id="lockTimer" className="bg-blue-50 p-3 rounded-md border border-blue-200">
                  <div className="flex items-center justify-between mb-1">
                    <span className="text-sm text-slate-900">Price Lock</span>
                    <Badge variant="outline">
                      00:{timeLeft.toString().padStart(2, '0')}
                    </Badge>
                  </div>
                  <p className="text-sm text-slate-600">
                    Demo lock only. Price refreshes every 30s.
                  </p>
                </div>

                <Button 
                  id="btnContinueToKyc"
                  className="w-full bg-blue-600" 
                  size="lg"
                  onClick={handleContinue}
                  disabled={!custody || !payment}
                >
                  Continue to KYC
                </Button>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Back Button */}
        <div className="mt-8">
          <Button variant="outline" onClick={() => navigate('/compare/gold?currency=USD')}>
            ← Back to Compare
          </Button>
        </div>
      </div>
    </div>
  );
}
