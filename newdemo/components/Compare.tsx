import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { CheckCircle, TrendingUp } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Badge } from './ui/badge';
import { DisabledOverlay } from './DisabledOverlay';
import { api, QuotesResponse } from '../lib/api';

export function Compare() {
  const navigate = useNavigate();
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
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center gap-2 mb-2">
            <TrendingUp className="w-6 h-6 text-blue-600" />
            <h1 id="compareHeader" className="text-slate-900">
              Compare Gold Markets (1 oz, in USD)
            </h1>
          </div>
          <div className="flex gap-2">
            <Badge id="fxContextChip" variant="outline">
              FX: Synchronized @ 16:00 UTC (demo)
            </Badge>
            <Badge id="priceTypeChip" variant="outline">
              Price Type: Spot (demo)
            </Badge>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Main Table */}
          <div className="lg:col-span-2">
            <Card>
              <CardContent className="p-6">
                <Table id="compareTable">
                  <TableHeader>
                    <TableRow>
                      <TableHead>Market</TableHead>
                      <TableHead>Price (USD)</TableHead>
                      <TableHead>Local</TableHead>
                      <TableHead>FX Rate</TableHead>
                      <TableHead>Spread vs Cheapest</TableHead>
                      <TableHead>Status</TableHead>
                      <TableHead></TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {sortedMarkets.map((market) => {
                      const isMumbai = market.market_id === 'MUMBAI';
                      const spreadVsCheapest = market.price_usd - cheapestPrice;
                      
                      return (
                        <TableRow
                          key={market.market_id}
                          id={`row${market.market_name.replace(' ', '')}`}
                          className={isMumbai ? 'bg-green-50 border-l-4 border-green-500 isRecommended' : ''}
                        >
                          <TableCell>
                            <div className="flex items-center gap-2">
                              <span>{market.market_name}</span>
                              {market.verified && (
                                <CheckCircle className="w-4 h-4 text-green-600" />
                              )}
                              {spreadVsCheapest === 0 && (
                                <Badge className="bg-green-600 text-white">
                                  Cheapest now
                                </Badge>
                              )}
                            </div>
                          </TableCell>
                          <TableCell className="priceInUserCCY">
                            ${market.price_usd.toLocaleString()}
                          </TableCell>
                          <TableCell className="localPrice">
                            {market.local_currency} {market.local_price.toLocaleString()}
                          </TableCell>
                          <TableCell className="fxRateUsed">
                            {market.fx}
                          </TableCell>
                          <TableCell>
                            {spreadVsCheapest === 0 ? (
                              <span className="text-green-600">—</span>
                            ) : (
                              <span className="text-slate-600">
                                +${spreadVsCheapest.toFixed(2)}
                              </span>
                            )}
                          </TableCell>
                          <TableCell>
                            <Badge variant="default">
                              Open
                            </Badge>
                          </TableCell>
                          <TableCell>
                            <DisabledOverlay disabled={!isMumbai}>
                              <Button
                                className={`btnSelectMarket ${isMumbai ? 'bg-blue-600' : ''}`}
                                size="sm"
                                onClick={() => {
                                  // Store selected market data in sessionStorage
                                  sessionStorage.setItem('selectedMarket', JSON.stringify(market));
                                  navigate('/buy/gold?market=MUMBAI&currency=USD');
                                }}
                              >
                                Select
                              </Button>
                            </DisabledOverlay>
                          </TableCell>
                        </TableRow>
                      );
                    })}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </div>

          {/* Side Card */}
          <div className="lg:col-span-1">
            <Card id="explainCard">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CheckCircle className="w-5 h-5 text-green-600" />
                  Verified by GVEN
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div>
                  <h3 className="text-slate-900 mb-2">All prices shown in USD</h3>
                  <p className="text-sm text-slate-600">
                    All prices are converted to USD using synchronized FX rates updated at 16:00 UTC. This ensures fair comparison across all global markets.
                  </p>
                </div>
                <div>
                  <h3 className="text-slate-900 mb-2">How we verify markets</h3>
                  <p className="text-sm text-slate-600">
                    Every market is verified for authenticity, regulatory compliance, and real-time price feeds. Only trusted brokers appear on GVEN.
                  </p>
                </div>
                <div>
                  <h3 className="text-slate-900 mb-2">Why prices vary</h3>
                  <p className="text-sm text-slate-600">
                    Market prices differ due to local demand, liquidity, broker fees, and timing of trades. GVEN helps you find the best deal globally.
                  </p>
                </div>
                <div className="bg-blue-50 p-3 rounded-md border border-blue-200">
                  <p className="text-sm text-slate-900">
                    💡 <strong>Mumbai currently {((sortedMarkets[sortedMarkets.length - 1].price_usd - cheapestPrice) / cheapestPrice * 100).toFixed(1)}% cheaper than {sortedMarkets[sortedMarkets.length - 1].market_name}</strong>
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Back Button */}
        <div className="mt-8">
          <Button variant="outline" onClick={() => navigate('/')}>
            ← Back to Markets
          </Button>
        </div>
      </div>
    </div>
  );
}
