import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { TrendingUp, CheckCircle, Clock } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { Badge } from './ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { DisabledOverlay } from './DisabledOverlay';
import { assets, currencies } from '../lib/mockData';
import { api, QuotesResponse } from '../lib/api';

export function Landing() {
  const navigate = useNavigate();
  const [selectedAsset] = useState('GOLD');
  const [selectedCurrency] = useState('USD');
  const [quotesData, setQuotesData] = useState<QuotesResponse | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.getQuotes('USD').then(data => {
      setQuotesData(data);
      setLoading(false);
    });
  }, []);

  if (loading || !quotesData) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
        <p className="text-slate-600">Loading markets...</p>
      </div>
    );
  }

  const sortedMarkets = [...quotesData.markets].sort((a, b) => a.price_usd - b.price_usd);
  const cheapestPrice = sortedMarkets[0].price_usd;

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="max-w-7xl mx-auto px-4 py-12">
        {/* Hero Section */}
        <div className="text-center mb-12">
          <div className="inline-flex items-center gap-2 mb-4">
            <TrendingUp className="w-8 h-8 text-blue-600" />
            <h1 id="heroTitle" className="text-slate-900">
              Global Value & Execution Network
            </h1>
          </div>
          <p className="text-slate-600 max-w-2xl mx-auto mb-8">
            See live global prices in your currency. Buy instantly on the cheapest verified market.
          </p>

          {/* Asset & Currency Selectors */}
          <div className="flex items-center justify-center gap-4 mb-8">
            <div className="flex gap-2">
              {assets.map((asset) => (
                <DisabledOverlay key={asset.id} disabled={!asset.enabled}>
                  <Button
                    id={asset.id === 'GOLD' ? 'assetSelector' : undefined}
                    variant={selectedAsset === asset.id ? 'default' : 'outline'}
                    className={selectedAsset === asset.id ? 'bg-blue-600' : ''}
                  >
                    {asset.name}
                  </Button>
                </DisabledOverlay>
              ))}
            </div>
            <div className="flex gap-2">
              {currencies.map((currency) => (
                <DisabledOverlay key={currency.id} disabled={!currency.enabled}>
                  <Button
                    id={currency.id === 'USD' ? 'currencySelector' : undefined}
                    variant={selectedCurrency === currency.id ? 'default' : 'outline'}
                    className={selectedCurrency === currency.id ? 'bg-blue-600' : ''}
                  >
                    {currency.id}
                  </Button>
                </DisabledOverlay>
              ))}
            </div>
          </div>
        </div>

        {/* Market Ticker */}
        <div id="marketTicker" className="space-y-3 mb-8">
          <h2 className="text-slate-900 mb-4">Live Gold Prices (1 oz)</h2>
          {sortedMarkets.map((market) => {
            const spread = market.price_usd - cheapestPrice;
            const isCheapest = spread === 0;
            
            return (
              <Card 
                key={market.market_id} 
                className={`transition-all hover:shadow-md ${
                  isCheapest ? 'border-green-500 border-2 bg-green-50' : ''
                }`}
              >
                <CardContent className="flex items-center justify-between p-4">
                  <div className="flex items-center gap-4">
                    <div className="flex flex-col">
                      <div className="flex items-center gap-2">
                        <span className="marketName">{market.market_name}</span>
                        {market.verified && (
                          <CheckCircle className="w-4 h-4 text-green-600 marketBadgeVerified" />
                        )}
                        {isCheapest && (
                          <Badge variant="secondary" className="bg-green-600 text-white">
                            Cheapest
                          </Badge>
                        )}
                      </div>
                      <div className="flex items-center gap-2 text-sm text-slate-500">
                        <span className="localPrice">
                          {market.local_currency} {market.local_price.toLocaleString()}
                        </span>
                        <span className="fxRateUsed">
                          @ {market.fx}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center gap-6">
                    <div className="text-right">
                      <div className="priceInUserCCY text-slate-900">
                        ${market.price_usd.toLocaleString()}
                      </div>
                      {spread > 0 && (
                        <div className="text-sm text-slate-500">
                          +${spread.toFixed(2)} vs cheapest
                        </div>
                      )}
                    </div>
                    <div className="flex items-center gap-1 text-sm text-slate-500 lastUpdated">
                      <Clock className="w-3 h-3" />
                      <span>Live</span>
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* CTAs */}
        <div className="flex items-center justify-center gap-4">
          <Button
            id="btnCompare"
            size="lg"
            className="bg-blue-600"
            onClick={() => navigate('/compare/gold?currency=USD')}
          >
            Compare Markets
          </Button>
          
          <Dialog>
            <DialogTrigger asChild>
              <Button id="btnHowItWorks" variant="outline" size="lg">
                How It Works
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle>How GVEN Works</DialogTitle>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="flex gap-4">
                  <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">
                    1
                  </div>
                  <div>
                    <h3 className="text-slate-900 mb-1">See Live Prices</h3>
                    <p className="text-slate-600">
                      View real-time prices from verified global markets, all converted to your currency using synchronized FX rates.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">
                    2
                  </div>
                  <div>
                    <h3 className="text-slate-900 mb-1">Compare & Choose</h3>
                    <p className="text-slate-600">
                      Markets are ranked by price. We automatically highlight the cheapest option for your selected asset.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">
                    3
                  </div>
                  <div>
                    <h3 className="text-slate-900 mb-1">Configure Order</h3>
                    <p className="text-slate-600">
                      Select quantity, custody preference (vault or delivery), and payment method.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">
                    4
                  </div>
                  <div>
                    <h3 className="text-slate-900 mb-1">Quick KYC</h3>
                    <p className="text-slate-600">
                      Complete a streamlined verification process to comply with global regulations.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">
                    5
                  </div>
                  <div>
                    <h3 className="text-slate-900 mb-1">Secure Payment</h3>
                    <p className="text-slate-600">
                      Pay with card or bank transfer. Your FX rate is locked in during checkout.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex-shrink-0 w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">
                    6
                  </div>
                  <div>
                    <h3 className="text-slate-900 mb-1">Asset Delivered</h3>
                    <p className="text-slate-600">
                      Your purchase appears in your wallet. Track live P/L and manage your holdings.
                    </p>
                  </div>
                </div>
              </div>
            </DialogContent>
          </Dialog>
        </div>
      </div>
    </div>
  );
}
