import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { CheckCircle, TrendingUp, Activity } from 'lucide-react';
import { Line, LineChart, CartesianGrid, XAxis, YAxis, ReferenceLine } from 'recharts';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Badge } from './ui/badge';
import { DisabledOverlay } from './DisabledOverlay';
import { api, QuotesResponse } from '../lib/api';
import {
  ChartContainer,
  ChartLegend,
  ChartLegendContent,
  ChartTooltip,
  ChartTooltipContent,
  type ChartConfig
} from './ui/chart';

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
  const recommendedMarketId = 'MILAN';
  const recommendedMarket = sortedMarkets.find((market) => market.market_id === recommendedMarketId) || sortedMarkets[0];
  const nextBestMarket = sortedMarkets[1];
  const highestMarket = sortedMarkets[sortedMarkets.length - 1];

  const spreadToNext = nextBestMarket ? nextBestMarket.price_usd - recommendedMarket.price_usd : 0;
  const spreadToHighest = highestMarket ? highestMarket.price_usd - recommendedMarket.price_usd : 0;
  const spreadToHighestPct = highestMarket
    ? ((spreadToHighest) / highestMarket.price_usd) * 100
    : 0;

  const palette = ['#f59e0b', '#2563eb', '#22c55e', '#a855f7', '#ef4444', '#0ea5e9'];
  const chartConfig = sortedMarkets.reduce<ChartConfig>((acc, market, index) => {
    acc[market.market_id] = {
      label: market.market_name,
      color: palette[index % palette.length]
    };
    return acc;
  }, {} as ChartConfig);

  const dateFormatter = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' });
  const chartData = quotesData.history.map((point) => ({
    timestamp: point.timestamp,
    label: dateFormatter.format(new Date(point.timestamp)),
    ...point.prices
  }));

  const chartNumericValues = chartData.flatMap((point) =>
    Object.entries(point)
      .filter(([key]) => key !== 'timestamp' && key !== 'label')
      .map(([, value]) => value as number)
  );
  const minY = chartNumericValues.length
    ? Math.min(...chartNumericValues) - 8
    : recommendedMarket.price_usd - 20;
  const maxY = chartNumericValues.length
    ? Math.max(...chartNumericValues) + 8
    : recommendedMarket.price_usd + 20;

  const fxFixTimestamp = new Date(quotesData.fx_context.timestamp).toLocaleString();

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
            <Badge id="selectedMarketChip" className="bg-amber-500 text-white">
              Demo focus: Milan (Italy)
            </Badge>
          </div>
        </div>

        <Card id="marketAnalyticsCard" className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Activity className="w-5 h-5 text-amber-500" />
              Market analytics snapshot
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid gap-6 lg:grid-cols-[3fr,2fr]">
              <ChartContainer config={chartConfig} className="bg-slate-900/5 rounded-lg">
                <LineChart data={chartData} margin={{ left: 12, right: 12, top: 12, bottom: 12 }}>
                  <CartesianGrid strokeDasharray="4 4" stroke="hsl(215, 25%, 80%)" opacity={0.5} />
                  <XAxis dataKey="label" tickLine={false} axisLine={false} />
                  <YAxis domain={[minY, maxY]} tickLine={false} axisLine={false} tickFormatter={(value) => `$${value.toFixed(0)}`} />
                  <ReferenceLine y={recommendedMarket.price_usd} stroke="var(--color-MILAN)" strokeDasharray="4 4" />
                  <ChartTooltip
                    cursor={{ strokeDasharray: '4 4' }}
                    content={
                      <ChartTooltipContent
                        labelFormatter={(value) => value as string}
                        formatter={(value, name) => {
                          if (typeof value !== 'number') {
                            return [value, name];
                          }
                          const market = sortedMarkets.find((m) => m.market_id === name);
                          return [`$${value.toFixed(2)}`, market?.market_name || name];
                        }}
                      />
                    }
                  />
                  <ChartLegend content={<ChartLegendContent />} />
                  {sortedMarkets.map((market) => (
                    <Line
                      key={market.market_id}
                      type="monotone"
                      dataKey={market.market_id}
                      stroke={`var(--color-${market.market_id})`}
                      strokeWidth={2}
                      dot={false}
                      activeDot={{ r: 4 }}
                    />
                  ))}
                </LineChart>
              </ChartContainer>

              <div className="space-y-4">
                <div className="bg-amber-50 border border-amber-200 rounded-lg p-4">
                  <h3 className="text-slate-900 font-semibold mb-2">Milan leads the pack</h3>
                  <p className="text-sm text-slate-700">
                    Milan is the demo-selected market at ${recommendedMarket.price_usd.toFixed(2)} per oz. That's
                    {spreadToNext > 0 ? ` $${spreadToNext.toFixed(2)} cheaper than ${nextBestMarket?.market_name}` : ' aligned with the next market'}
                    {spreadToHighest > 0 ? ` and ${(spreadToHighestPct).toFixed(2)}% below ${highestMarket?.market_name}.` : '.'}
                  </p>
                </div>
                <div className="space-y-2 text-sm text-slate-700">
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Base currency</span>
                    <span>{quotesData.currency}</span>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className="font-medium">FX fix timestamp</span>
                    <span>{fxFixTimestamp}</span>
                  </div>
                </div>
                <div className="border rounded-lg p-4">
                  <h3 className="text-slate-900 font-semibold mb-3">Conversion rates</h3>
                  <div className="space-y-2">
                    {sortedMarkets.map((market) => (
                      <div key={`fx-${market.market_id}`} className="flex items-center justify-between text-sm text-slate-600">
                        <span>{market.market_name} ({market.local_currency})</span>
                        <span>1 {market.local_currency} = {market.fx.toFixed(4)} USD</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

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
                      const isRecommended = market.market_id === recommendedMarketId;
                      const spreadVsCheapest = market.price_usd - cheapestPrice;

                      return (
                        <TableRow
                          key={market.market_id}
                          id={`row${market.market_name.replace(' ', '')}`}
                          className={isRecommended ? 'bg-amber-50 border-l-4 border-amber-500 isRecommended' : ''}
                        >
                          <TableCell>
                            <div className="flex items-center gap-2">
                              <span>{market.market_name}</span>
                              {market.verified && (
                                <CheckCircle className="w-4 h-4 text-green-600" />
                              )}
                              {spreadVsCheapest === 0 && (
                                <Badge className="bg-amber-500 text-white">
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
                            <DisabledOverlay disabled={!isRecommended}>
                              <Button
                                className={`btnSelectMarket ${isRecommended ? 'bg-amber-500 hover:bg-amber-600 text-white' : ''}`}
                                size="sm"
                                onClick={() => {
                                  // Store selected market data in sessionStorage
                                  sessionStorage.setItem('selectedMarket', JSON.stringify(market));
                                  navigate('/buy/gold?market=MILAN&currency=USD');
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
            <Card id="fxCard" className="mb-6">
              <CardHeader>
                <CardTitle>FX context</CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 text-sm text-slate-600">
                <p>
                  All markets converted to <strong>{quotesData.currency}</strong> using a synchronized demo fix at {fxFixTimestamp}.
                </p>
                <div className="space-y-2">
                  {sortedMarkets.map((market) => (
                    <div key={`fxdetails-${market.market_id}`} className="flex items-center justify-between">
                      <span>{market.market_name}</span>
                      <span className="font-mono">{market.fx.toFixed(4)}</span>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
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
                {highestMarket && (
                  <div className="bg-blue-50 p-3 rounded-md border border-blue-200">
                    <p className="text-sm text-slate-900">
                      💡 <strong>{recommendedMarket.market_name} currently {spreadToHighestPct.toFixed(1)}% cheaper than {highestMarket.market_name}</strong>
                    </p>
                  </div>
                )}
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
