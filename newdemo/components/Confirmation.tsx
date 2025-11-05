import { useNavigate, useSearchParams } from 'react-router-dom';
import { CheckCircle, Download, ArrowRight } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Badge } from './ui/badge';
import { api } from '../lib/api';

export function Confirmation() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const orderId = searchParams.get('orderId') || 'GVEN-DEMO-0001';

  // Get order response from sessionStorage
  const orderResponseStr = sessionStorage.getItem('orderResponse');
  const orderResponse = orderResponseStr ? JSON.parse(orderResponseStr) : null;
  
  const orderConfigStr = sessionStorage.getItem('orderConfig');
  const orderConfig = orderConfigStr ? JSON.parse(orderConfigStr) : null;

  if (!orderResponse || !orderConfig) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
        <div className="text-center">
          <p className="text-slate-600 mb-4">Order not found. Please start over.</p>
          <Button onClick={() => navigate('/')}>Return to Home</Button>
        </div>
      </div>
    );
  }

  const { market, quantity, basePrice, fees, total } = orderConfig;
  const timestamp = new Date(orderResponse.timestamp).toLocaleString();

  const handleDownloadReceipt = () => {
    const receiptUrl = api.getReceiptUrl(orderId);
    // In a real app, this would trigger a download
    alert(`Receipt would download from: ${receiptUrl}\n\nThis is a demo feature.`);
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="max-w-4xl mx-auto px-4 py-12">
        {/* Success Header */}
        <div className="text-center mb-8">
          <div id="successIcon" className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <CheckCircle className="w-12 h-12 text-green-600" />
          </div>
          <h1 id="confirmTitle" className="text-slate-900 mb-2">Purchase Confirmed!</h1>
          <p className="text-slate-600">
            Your gold purchase has been successfully executed
          </p>
          <Badge variant="outline" className="mt-2">
            Order ID: {orderId}
          </Badge>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Details */}
          <div className="lg:col-span-2 space-y-6">
            <Card>
              <CardHeader>
                <CardTitle>Transaction Details</CardTitle>
              </CardHeader>
              <CardContent id="tradeDetails" className="space-y-4">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Order ID</p>
                    <p className="text-slate-900">{orderResponse.order_id}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Timestamp</p>
                    <p className="text-slate-900">{timestamp}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Asset</p>
                    <p className="text-slate-900">Gold</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Qty</p>
                    <p className="text-slate-900">{orderResponse.filled_qty_oz} oz</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Market</p>
                    <p className="text-slate-900">{market.market_name}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Local Price</p>
                    <p className="text-slate-900">{market.local_currency} {market.local_price.toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">FX</p>
                    <p className="text-slate-900">{market.fx}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Total (USD)</p>
                    <p className="text-slate-900">${total.toFixed(2)}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Custody</p>
                    <p className="text-slate-900">Vault {market.market_name}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Broker Partner</p>
                    <p className="text-slate-900">{orderResponse.broker}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Settlement Ref</p>
                    <p className="text-slate-900">{orderResponse.settlement_ref}</p>
                  </div>
                  <div>
                    <p className="text-sm text-slate-600 mb-1">Status</p>
                    <Badge className="bg-green-600 text-white">{orderResponse.status}</Badge>
                  </div>
                </div>

                <div className="bg-green-50 p-4 rounded-md border border-green-200">
                  <h3 className="text-slate-900 mb-2 flex items-center gap-2">
                    <CheckCircle className="w-4 h-4 text-green-600" />
                    Vault Allocation Confirmed
                  </h3>
                  <p className="text-sm text-slate-600">
                    Your {quantity} oz of gold has been securely stored in our {market.market_name} vault facility. You can view and manage your holdings in your wallet.
                  </p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Fee Breakdown</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Base Price</span>
                    <span className="text-slate-900">${basePrice.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Broker Fee</span>
                    <span className="text-slate-900">${fees.broker.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-slate-600">Spread Fee (0.1%)</span>
                    <span className="text-slate-900">${fees.spread.toFixed(2)}</span>
                  </div>
                  <div className="border-t pt-2">
                    <div className="flex justify-between">
                      <span className="text-slate-900">Total Fees</span>
                      <span className="text-slate-900">${fees.total.toFixed(2)}</span>
                    </div>
                  </div>
                  <div className="border-t pt-2 mt-2">
                    <div className="flex justify-between">
                      <span className="text-slate-900">Grand Total</span>
                      <span className="text-slate-900">${total.toFixed(2)}</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Actions */}
          <div className="lg:col-span-1 space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Next Steps</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3">
                <Button
                  id="btnViewWallet"
                  className="w-full bg-blue-600"
                  onClick={() => navigate('/wallet')}
                >
                  View Wallet
                  <ArrowRight className="w-4 h-4 ml-2" />
                </Button>
                <Button 
                  id="btnDownloadReceipt" 
                  variant="outline" 
                  className="w-full"
                  onClick={handleDownloadReceipt}
                >
                  <Download className="w-4 h-4 mr-2" />
                  Download Receipt
                </Button>
                <Button variant="outline" className="w-full" onClick={() => navigate('/')}>
                  Make Another Purchase
                </Button>
              </CardContent>
            </Card>

            <Card className="bg-blue-50 border-blue-200">
              <CardContent className="p-4">
                <h3 className="text-slate-900 mb-2">What's Next?</h3>
                <ul className="text-sm text-slate-600 space-y-1">
                  <li>• View your asset in the Wallet</li>
                  <li>• Track real-time P/L</li>
                  <li>• Manage holdings</li>
                  <li>• Sell or transfer anytime</li>
                </ul>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  );
}
