import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { CreditCard, Lock, Loader2 } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Badge } from './ui/badge';
import { api, savePosition } from '../lib/api';

export function Payment() {
  const navigate = useNavigate();
  const [cardData, setCardData] = useState({
    number: '',
    name: '',
    expiry: '',
    cvv: ''
  });
  const [isProcessing, setIsProcessing] = useState(false);

  // Get order config from sessionStorage
  const orderConfigStr = sessionStorage.getItem('orderConfig');
  const orderConfig = orderConfigStr ? JSON.parse(orderConfigStr) : null;
  
  // Get KYC data from sessionStorage
  const kycDataStr = sessionStorage.getItem('kycData');
  const kycData = kycDataStr ? JSON.parse(kycDataStr) : null;

  if (!orderConfig || !kycData) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
        <div className="text-center">
          <p className="text-slate-600 mb-4">Session expired. Please start over.</p>
          <Button onClick={() => navigate('/')}>Return to Home</Button>
        </div>
      </div>
    );
  }

  const { market, quantity, basePrice, fees, total } = orderConfig;

  const isValid = cardData.number && cardData.name && cardData.expiry && cardData.cvv;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isValid) {
      setIsProcessing(true);
      
      try {
        // Tokenize payment
        const tokenResponse = await api.tokenizePayment(cardData);
        
        // Execute order
        const orderResponse = await api.executeOrder({
          qty_oz: quantity,
          market_id: market.market_id,
          market_name: market.market_name,
          price_usd: basePrice,
          custody: 'VAULT_MILAN',
          payment_token: tokenResponse.token
        });
        
        // Save position to localStorage
        savePosition(orderResponse, total);
        
        // Store order response in sessionStorage for confirmation page
        sessionStorage.setItem('orderResponse', JSON.stringify(orderResponse));
        
        navigate(`/confirm?orderId=${orderResponse.order_id}`);
      } catch (error) {
        console.error('Payment failed:', error);
        setIsProcessing(false);
      }
    }
  };

  if (isProcessing) {
    return (
      <div id="paymentProcessing" className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
        <Card className="max-w-md">
          <CardContent className="p-8 text-center">
            <Loader2 className="w-12 h-12 text-blue-600 animate-spin mx-auto mb-4" />
            <h2 className="text-slate-900 mb-2">Processing Payment</h2>
            <p className="text-slate-600">
              Locking rate and executing via partner broker...
            </p>
          </CardContent>
        </Card>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="max-w-5xl mx-auto px-4 py-12">
        <div className="mb-8">
          <div className="flex items-center gap-2 mb-2">
            <CreditCard className="w-6 h-6 text-blue-600" />
            <h1 className="text-slate-900">Secure Payment</h1>
          </div>
          <p className="text-slate-600">
            Complete your purchase with encrypted card payment
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left Column - Payment Form */}
          <div className="lg:col-span-2">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Lock className="w-5 h-5 text-green-600" />
                  Card Details
                  <Badge variant="outline" className="ml-auto">3D Secure Protected</Badge>
                </CardTitle>
              </CardHeader>
              <CardContent>
                <form id="cardForm" onSubmit={handleSubmit} className="space-y-4">
                  <div>
                    <Label htmlFor="cardNumber">Card Number *</Label>
                    <Input
                      id="cardNumber"
                      placeholder="4242 4242 4242 4242"
                      value={cardData.number}
                      onChange={(e) => {
                        const value = e.target.value.replace(/\s/g, '');
                        const formatted = value.match(/.{1,4}/g)?.join(' ') || value;
                        setCardData({ ...cardData, number: formatted });
                      }}
                      maxLength={19}
                      className="mt-2"
                      required
                    />
                    <p className="text-sm text-slate-500 mt-1">Demo: Use 4242 4242 4242 4242</p>
                  </div>

                  <div>
                    <Label htmlFor="cardName">Cardholder Name *</Label>
                    <Input
                      id="cardName"
                      placeholder="JOHN DOE"
                      value={cardData.name}
                      onChange={(e) => setCardData({ ...cardData, name: e.target.value.toUpperCase() })}
                      className="mt-2"
                      required
                    />
                  </div>

                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <Label htmlFor="cardExpiry">Expiry Date *</Label>
                      <Input
                        id="cardExpiry"
                        placeholder="MM/YY"
                        value={cardData.expiry}
                        onChange={(e) => {
                          let value = e.target.value.replace(/\D/g, '');
                          if (value.length >= 2) {
                            value = value.slice(0, 2) + '/' + value.slice(2, 4);
                          }
                          setCardData({ ...cardData, expiry: value });
                        }}
                        maxLength={5}
                        className="mt-2"
                        required
                      />
                    </div>
                    <div>
                      <Label htmlFor="cardCVC">CVV/CVC *</Label>
                      <Input
                        id="cardCVC"
                        type="password"
                        placeholder="123"
                        value={cardData.cvv}
                        onChange={(e) => setCardData({ ...cardData, cvv: e.target.value.replace(/\D/g, '') })}
                        maxLength={3}
                        className="mt-2"
                        required
                      />
                    </div>
                  </div>

                  <div id="billingAddress" className="bg-slate-50 p-4 rounded-md">
                    <h3 className="text-slate-900 mb-2">Billing Address</h3>
                    <p className="text-sm text-slate-600">
                      {kycData.fullName}<br />
                      {kycData.addressLine1}<br />
                      {kycData.country}
                    </p>
                    <p className="text-sm text-slate-500 mt-2">Auto-filled from KYC</p>
                  </div>

                  <div className="bg-slate-50 p-4 rounded-md">
                    <h3 className="text-slate-900 mb-2 flex items-center gap-2">
                      <Lock className="w-4 h-4" />
                      Security Notice
                    </h3>
                    <p className="text-sm text-slate-600">
                      This is a demo payment form. In production, card details would be tokenized and processed through a PCI-DSS compliant payment gateway. Never enter real card details in demo environments.
                    </p>
                  </div>

                  <div className="flex gap-4 pt-4">
                    <Button
                      type="button"
                      variant="outline"
                      onClick={() => navigate('/kyc?next=/pay')}
                    >
                      ← Back
                    </Button>
                    <Button
                      id="btnPayNow"
                      type="submit"
                      className="flex-1 bg-blue-600"
                      disabled={!isValid}
                    >
                      Pay ${total.toFixed(2)}
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Order Summary */}
          <div className="lg:col-span-1">
            <Card id="paySummary" className="sticky top-4">
              <CardHeader>
                <CardTitle>Payment Summary</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Asset</span>
                    <span className="text-slate-900">Gold ({quantity} oz)</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Market</span>
                    <span className="text-slate-900">{market.market_name}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Unit Price</span>
                    <span className="text-slate-900">${market.price_usd.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">FX Rate</span>
                    <span className="text-slate-900">{market.fx}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Custody</span>
                    <span className="text-slate-900">Vault</span>
                  </div>
                  <div className="border-t pt-2 mt-2"></div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Subtotal</span>
                    <span className="text-slate-900">${basePrice.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Fees</span>
                    <span className="text-slate-900">${fees.total.toFixed(2)}</span>
                  </div>
                  <div className="border-t pt-2 mt-2">
                    <div className="flex justify-between">
                      <span className="text-slate-900">Total (USD)</span>
                      <span className="text-slate-900">${total.toFixed(2)}</span>
                    </div>
                  </div>
                </div>

                <div className="bg-green-50 p-3 rounded-md">
                  <p className="text-sm text-green-800">
                    ✓ Cheapest market selected<br />
                    ✓ FX rate locked<br />
                    ✓ Secure vault storage
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}
