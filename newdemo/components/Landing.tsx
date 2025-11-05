import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Badge } from './ui/badge';
import { Button } from './ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from './ui/card';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Switch } from './ui/switch';
import { DisabledOverlay } from './DisabledOverlay';
import { TrendingUp, CheckCircle, Clock, Layers, Settings2, Plus } from 'lucide-react';
import { assets, currencies } from '../lib/mockData';
import { api, QuotesResponse } from '../lib/api';

type AssetPricing = {
  assetId: string;
  markets: Array<{
    market_id: string;
    market_name: string;
    local_currency: string;
    local_price: number;
    fx: number;
    verified: boolean;
    updated: string;
    priceUSD: number;
    priceInCurrency: number;
  }>;
};

const assetBasePriceUSD: Record<string, number> = {
  GOLD: 3988.5,
  SILVER: 28.45,
  PLATINUM: 965.75,
  PALLADIUM: 1118.3,
};

const currencyRates: Record<string, number> = {
  USD: 1,
  EUR: 0.92,
  GBP: 0.79,
  INR: 83.11,
  CNY: 7.24,
};

export function Landing() {
  const navigate = useNavigate();
  const [selectedAsset] = useState('GOLD');
  const [selectedCurrency, setSelectedCurrency] = useState('USD');
  const [quotesData, setQuotesData] = useState<QuotesResponse | null>(null);
  const [selectedMarkets, setSelectedMarkets] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    api.getQuotes(selectedCurrency).then((data) => {
      setQuotesData(data);
      setLoading(false);
    });
  }, [selectedCurrency]);

  useEffect(() => {
    if (quotesData) {
      setSelectedMarkets(quotesData.markets.map((market) => market.market_id));
    }
  }, [quotesData]);

  const sortedMarkets = useMemo(() => {
    if (!quotesData) {
      return [] as QuotesResponse['markets'];
    }

    return [...quotesData.markets].sort((a, b) => a.price_usd - b.price_usd);
  }, [quotesData]);

  const priceFormatter = useMemo(
    () =>
      new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: selectedCurrency,
        maximumFractionDigits: selectedCurrency === 'INR' ? 0 : 2,
      }),
    [selectedCurrency]
  );

  const assetPricing = useMemo<AssetPricing[]>(() => {
    if (!sortedMarkets.length) {
      return [];
    }

    const cheapestGold = sortedMarkets[0]?.price_usd ?? 1;
    const fx = currencyRates[selectedCurrency] ?? 1;

    return assets.map((asset) => {
      const basePriceUSD = assetBasePriceUSD[asset.id] ?? cheapestGold;

      const markets = sortedMarkets.map((market) => {
        const relativeSpread = cheapestGold > 0 ? market.price_usd / cheapestGold : 1;
        const priceUSD = basePriceUSD * relativeSpread;
        const priceInCurrency = priceUSD * fx;

        return {
          market_id: market.market_id,
          market_name: market.market_name,
          local_currency: market.local_currency,
          local_price: market.local_price,
          fx: market.fx,
          verified: market.verified,
          updated: market.updated,
          priceUSD,
          priceInCurrency,
        };
      });

      return { assetId: asset.id, markets };
    });
  }, [sortedMarkets, selectedCurrency]);

  const handleToggleMarket = (marketId: string, checked: boolean) => {
    setSelectedMarkets((current) => {
      if (checked) {
        if (current.includes(marketId)) {
          return current;
        }
        return [...current, marketId];
      }

      if (current.length <= 1) {
        return current;
      }

      return current.filter((id) => id !== marketId);
    });
  };

  if (loading || !quotesData) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100">
        <p className="text-slate-600">Loading markets...</p>
      </div>
    );
  }

  const selectedCurrencyMeta = currencies.find((currency) => currency.id === selectedCurrency);

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="mx-auto max-w-7xl px-4 py-12">
        <div className="mb-12 text-center">
          <div className="mb-4 inline-flex items-center gap-2">
            <TrendingUp className="h-8 w-8 text-blue-600" />
            <h1 id="heroTitle" className="text-3xl font-semibold text-slate-900 md:text-4xl">
              Global Value & Execution Network
            </h1>
          </div>
          <p className="mx-auto mb-8 max-w-2xl text-slate-600">
            Monitor multiple assets side by side. All pricing is synchronized to your default currency{' '}
            <span className="font-semibold text-slate-700">({selectedCurrencyMeta?.id ?? 'USD'})</span> and ranked by the
            cheapest verified market.
          </p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <div className="flex flex-wrap items-center justify-center gap-2">
              {assets.map((asset) => (
                <DisabledOverlay key={asset.id} disabled={!asset.enabled}>
                  <Button
                    id={asset.id === selectedAsset ? 'assetSelector' : undefined}
                    variant={selectedAsset === asset.id ? 'default' : 'outline'}
                    className={`${selectedAsset === asset.id ? 'bg-blue-600 text-white' : 'bg-white/80 text-slate-700'} min-w-[5.5rem]`}
                  >
                    {asset.name}
                  </Button>
                </DisabledOverlay>
              ))}
            </div>
            <div className="flex flex-wrap items-center justify-center gap-2">
              {currencies.map((currency) => (
                <Button
                  key={currency.id}
                  id={currency.id === selectedCurrency ? 'currencySelector' : undefined}
                  variant={selectedCurrency === currency.id ? 'default' : 'outline'}
                  className={`${selectedCurrency === currency.id ? 'bg-blue-600 text-white' : 'bg-white/80 text-slate-700'} min-w-[4.5rem]`}
                  onClick={() => setSelectedCurrency(currency.id)}
                >
                  {currency.id}
                </Button>
              ))}
            </div>
          </div>
        </div>

        <div className="grid gap-8 xl:grid-cols-[3fr,2fr]">
          <section className="space-y-6">
            <div className="flex items-center gap-3 text-slate-900">
              <Layers className="h-5 w-5 text-blue-600" />
              <div>
                <h2 className="text-xl font-semibold">Asset market grid</h2>
                <p className="text-sm text-slate-500">
                  Every card shows how that asset is priced across the verified markets you have enabled below.
                </p>
              </div>
            </div>

            <div className="grid gap-5 md:grid-cols-2">
              {assetPricing.map((pricing) => {
                const asset = assets.find((item) => item.id === pricing.assetId);
                if (!asset) {
                  return null;
                }

                const cheapestPriceUSD = pricing.markets.reduce(
                  (cheapest, market) => Math.min(cheapest, market.priceUSD),
                  Number.POSITIVE_INFINITY
                );

                const marketsToShow =
                  pricing.assetId === selectedAsset
                    ? pricing.markets.filter((market) => selectedMarkets.includes(market.market_id))
                    : pricing.markets.slice(0, 4);

                return (
                  <DisabledOverlay key={pricing.assetId} disabled={!asset.enabled}>
                    <Card
                      className={`h-full bg-white/80 backdrop-blur ${
                        pricing.assetId === selectedAsset ? 'border-blue-200 shadow-lg shadow-blue-100' : ''
                      }`}
                    >
                      <CardHeader className="pb-4">
                        <CardTitle className="flex items-center justify-between text-lg text-slate-900">
                          <span>{asset.name}</span>
                          <span className="text-sm font-medium text-slate-500">{asset.unit}</span>
                        </CardTitle>
                        <CardDescription className="text-sm text-slate-500">
                          {pricing.assetId === selectedAsset
                            ? 'Live markets using your personalized view.'
                            : 'Demo preview — select Gold to continue the guided journey.'}
                        </CardDescription>
                      </CardHeader>
                      <CardContent className="space-y-3">
                        {marketsToShow.length === 0 ? (
                          <p className="text-sm text-slate-500">Use the toggles to add markets to your grid.</p>
                        ) : (
                          marketsToShow.map((market) => {
                            const isCheapest = Math.abs(market.priceUSD - cheapestPriceUSD) < 0.5;

                            return (
                              <div
                                key={market.market_id}
                                className={`rounded-xl border p-3 transition-shadow ${
                                  isCheapest ? 'border-green-500 bg-green-50/80 shadow-sm' : 'border-slate-200 bg-white'
                                }`}
                              >
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                  <div className="space-y-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                      <span className="marketName font-medium text-slate-900">{market.market_name}</span>
                                      {market.verified && (
                                        <CheckCircle className="marketBadgeVerified h-4 w-4 text-green-600" />
                                      )}
                                      {isCheapest && (
                                        <Badge variant="secondary" className="bg-green-600 text-white">
                                          Cheapest
                                        </Badge>
                                      )}
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                      <span className="localPrice">
                                        {market.local_currency} {market.local_price.toLocaleString()}
                                      </span>
                                      <span className="fxRateUsed">@ {market.fx}</span>
                                    </div>
                                  </div>
                                  <div className="text-right">
                                    <div className="priceInUserCCY text-base font-semibold text-slate-900">
                                      {priceFormatter.format(market.priceInCurrency)}
                                    </div>
                                    <div className="text-xs text-slate-500">
                                      ${market.priceUSD.toLocaleString(undefined, { maximumFractionDigits: 2 })} USD
                                    </div>
                                  </div>
                                </div>
                                <div className="mt-2 flex items-center justify-between text-xs text-slate-500">
                                  <div className="flex items-center gap-1 lastUpdated">
                                    <Clock className="h-3 w-3" />
                                    <span>Live</span>
                                  </div>
                                  {pricing.assetId === selectedAsset && !selectedMarkets.includes(market.market_id) && (
                                    <span className="text-amber-600">Hidden</span>
                                  )}
                                </div>
                              </div>
                            );
                          })
                        )}
                      </CardContent>
                      {pricing.assetId === selectedAsset && (
                        <CardFooter className="justify-end pt-0">
                          <Button
                            id="btnCompare"
                            size="sm"
                            className="bg-blue-600"
                            onClick={() => navigate(`/compare/gold?currency=${selectedCurrency}`)}
                          >
                            Compare markets
                          </Button>
                        </CardFooter>
                      )}
                    </Card>
                  </DisabledOverlay>
                );
              })}
            </div>
          </section>

          <aside className="space-y-6">
            <Card className="bg-white/80 backdrop-blur">
              <CardHeader>
                <CardTitle className="flex items-center gap-2 text-slate-900">
                  <Settings2 className="h-5 w-5 text-blue-600" />
                  Default currency
                </CardTitle>
                <CardDescription>Switch to see how each market prices your assets.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex flex-wrap gap-2">
                  {currencies.map((currency) => (
                    <Button
                      key={currency.id}
                      id={currency.id === selectedCurrency ? 'currencySelector' : undefined}
                      variant={selectedCurrency === currency.id ? 'default' : 'outline'}
                      className={`${selectedCurrency === currency.id ? 'bg-blue-600 text-white' : 'bg-white text-slate-700'} min-w-[4.5rem]`}
                      onClick={() => setSelectedCurrency(currency.id)}
                    >
                      {currency.id}
                    </Button>
                  ))}
                </div>
                <p className="text-sm text-slate-500">
                  FX synchronized to {quotesData.fx_context.fix}. Pricing updates instantly when you change currencies.
                </p>
              </CardContent>
            </Card>

            <Card className="bg-white/80 backdrop-blur">
              <CardHeader>
                <CardTitle className="text-slate-900">Manage markets</CardTitle>
                <CardDescription>Toggle which venues appear in your grid.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                {sortedMarkets.map((market) => (
                  <div
                    key={market.market_id}
                    className="flex items-center justify-between rounded-xl border border-slate-200 bg-white/70 px-4 py-3"
                  >
                    <div className="space-y-1">
                      <p className="font-medium text-slate-900">{market.market_name}</p>
                      <p className="text-xs text-slate-500">
                        {market.local_currency} {market.local_price.toLocaleString()} @ {market.fx}
                      </p>
                    </div>
                    <Switch
                      aria-label={`Toggle ${market.market_name}`}
                      checked={selectedMarkets.includes(market.market_id)}
                      onCheckedChange={(checked) => handleToggleMarket(market.market_id, checked)}
                    />
                  </div>
                ))}
                <DisabledOverlay disabled>
                  <Button variant="outline" className="w-full justify-start gap-2 text-slate-600">
                    <Plus className="h-4 w-4" /> Add custom market
                  </Button>
                </DisabledOverlay>
              </CardContent>
            </Card>
          </aside>
        </div>

        <div className="mt-12 flex flex-wrap items-center justify-center gap-4">
          <Button
            size="lg"
            className="bg-blue-600"
            onClick={() => navigate(`/compare/gold?currency=${selectedCurrency}`)}
          >
            Continue to compare flow
          </Button>

          <Dialog>
            <DialogTrigger asChild>
              <Button id="btnHowItWorks" variant="outline" size="lg">
                How this guided demo works
              </Button>
            </DialogTrigger>
            <DialogContent className="max-w-2xl">
              <DialogHeader>
                <DialogTitle>Follow the highlighted path</DialogTitle>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="flex gap-4">
                  <div className="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white">1</div>
                  <div>
                    <h3 className="text-slate-900">Explore the grid</h3>
                    <p className="text-slate-600">
                      Click on dimmed controls to see where to continue. The demo overlay darkens the screen and spotlights the
                      next action.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white">2</div>
                  <div>
                    <h3 className="text-slate-900">Activate the cheapest market</h3>
                    <p className="text-slate-600">
                      Use the market toggles to refine your view. Gold is preselected so you can move straight into the guided
                      purchase flow.
                    </p>
                  </div>
                </div>
                <div className="flex gap-4">
                  <div className="flex h-8 w-8 items-center justify-center rounded-full bg-blue-600 text-white">3</div>
                  <div>
                    <h3 className="text-slate-900">Complete the walkthrough</h3>
                    <p className="text-slate-600">
                      Compare markets, pass the abbreviated KYC check, and simulate a purchase to watch the asset appear in your
                      wallet.
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
