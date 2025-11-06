import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Wallet as WalletIcon, TrendingUp, TrendingDown, MapPin, Calendar, FileText } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Badge } from './ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { api, WalletResponse } from '../lib/api';
import { DisabledOverlay } from './DisabledOverlay';

export function Wallet() {
  const navigate = useNavigate();
  const [walletData, setWalletData] = useState<WalletResponse | null>(null);
  const [currentMark, setCurrentMark] = useState<number>(4012.20);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Load wallet data
    api.getWallet().then(data => {
      setWalletData(data);
      setLoading(false);
    });

    // Update mark price periodically
    const markInterval = setInterval(() => {
      api.getMark('GOLD').then(mark => {
        setCurrentMark(mark);
        
        // Update the position mark in wallet data
        setWalletData(prev => {
          if (!prev || prev.positions.length === 0) return prev;
          
          const updatedPositions = prev.positions.map(pos => ({
            ...pos,
            mark_usd: mark,
            pl_usd: mark - pos.cost_basis_usd,
            pl_pct: (mark - pos.cost_basis_usd) / pos.cost_basis_usd
          }));
          
          const updated = {
            ...prev,
            positions: updatedPositions
          };
          
          // Update localStorage
          localStorage.setItem('gven_position', JSON.stringify(updated));
          
          return updated;
        });
      });
    }, 2000);

    return () => clearInterval(markInterval);
  }, []);

  if (loading) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center">
        <p className="text-slate-600">Loading wallet...</p>
      </div>
    );
  }

  if (!walletData || walletData.positions.length === 0) {
    return (
      <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        <div className="max-w-6xl mx-auto px-4 py-12">
          <div className="mb-8">
            <div className="flex items-center gap-2 mb-2">
              <WalletIcon className="w-6 h-6 text-blue-600" />
              <h1 id="walletHeader" className="text-slate-900">Your Holdings (USD)</h1>
            </div>
            <p className="text-slate-600">
              Track your holdings and monitor real-time performance
            </p>
          </div>

          <Card>
            <CardContent className="p-12 text-center">
              <WalletIcon className="w-16 h-16 text-slate-300 mx-auto mb-4" />
              <h2 className="text-slate-900 mb-2">No Holdings Yet</h2>
              <p className="text-slate-600 mb-6">
                You haven't purchased any assets yet. Start by exploring available markets.
              </p>
              <Button className="bg-blue-600" onClick={() => navigate('/')}>
                Explore Markets
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    );
  }

  const position = walletData.positions[0]; // Demo has only one position
  const totalValue = position.mark_usd * position.qty_oz;
  const totalCost = position.cost_basis_usd;
  const profitLoss = position.pl_usd;
  const profitLossPercent = position.pl_pct * 100;
  const isProfit = profitLoss >= 0;

  const formatVaultLocation = (location: string) =>
    location
      .replace('VAULT_', '')
      .split('_')
      .map((part) => part.charAt(0) + part.slice(1).toLowerCase())
      .join(' ');

  const locationLabel = position.market_name || formatVaultLocation(position.location);

  // Get purchase date from stored order
  const storedOrder = localStorage.getItem('gven_order');
  const orderData = storedOrder ? JSON.parse(storedOrder) : null;
  const purchaseDate = orderData ? new Date(orderData.timestamp).toLocaleDateString() : new Date().toLocaleDateString();

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="max-w-6xl mx-auto px-4 py-12">
        {/* Header */}
        <div className="mb-8">
          <div className="flex items-center gap-2 mb-2">
            <WalletIcon className="w-6 h-6 text-blue-600" />
            <h1 id="walletHeader" className="text-slate-900">Your Holdings (USD)</h1>
          </div>
          <p className="text-slate-600">
            Track your holdings and monitor real-time performance
          </p>
        </div>

        {/* Portfolio Summary */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Total Value</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-slate-900">${totalValue.toFixed(2)}</p>
              <p className="text-sm text-slate-600">USD</p>
            </CardContent>
          </Card>
          
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Cost Basis</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-slate-900">${totalCost.toFixed(2)}</p>
              <p className="text-sm text-slate-600">Including fees</p>
            </CardContent>
          </Card>
          
          <Card className={isProfit ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}>
            <CardHeader>
              <CardTitle className="text-sm flex items-center gap-2">
                P/L
                {isProfit ? (
                  <TrendingUp className="w-4 h-4 text-green-600" />
                ) : (
                  <TrendingDown className="w-4 h-4 text-red-600" />
                )}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p className={`${isProfit ? 'text-green-600' : 'text-red-600'}`}>
                {isProfit ? '+' : ''}${profitLoss.toFixed(2)}
              </p>
              <p className={`text-sm ${isProfit ? 'text-green-600' : 'text-red-600'}`}>
                {isProfit ? '+' : ''}{profitLossPercent.toFixed(2)}%
              </p>
            </CardContent>
          </Card>
        </div>

        {/* Holdings Table */}
        <Card>
          <CardHeader>
            <CardTitle>Holdings</CardTitle>
          </CardHeader>
          <CardContent>
            <Table id="positionsTable">
              <TableHeader>
                <TableRow>
                  <TableHead>Asset</TableHead>
                  <TableHead>Qty</TableHead>
                  <TableHead>Location/Custody</TableHead>
                  <TableHead>Cost Basis (USD)</TableHead>
                  <TableHead>Mark (USD)</TableHead>
                  <TableHead>P/L (USD)</TableHead>
                  <TableHead></TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow>
                  <TableCell>
                    <div>
                      <div className="text-slate-900">{position.asset}</div>
                      <Badge variant="outline" className="mt-1">Physical Asset</Badge>
                    </div>
                  </TableCell>
                  <TableCell>{position.qty_oz.toFixed(3)}</TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      <MapPin className="w-4 h-4 text-slate-600" />
                      Vault – {locationLabel}
                    </div>
                  </TableCell>
                  <TableCell>${position.cost_basis_usd.toFixed(2)}</TableCell>
                  <TableCell>${position.mark_usd.toFixed(2)}</TableCell>
                  <TableCell className={isProfit ? 'text-green-600' : 'text-red-600'}>
                    {isProfit ? '+' : ''}${position.pl_usd.toFixed(2)} ({isProfit ? '+' : ''}{profitLossPercent.toFixed(2)}%)
                  </TableCell>
                  <TableCell>
                    <div id="positionActions" className="flex gap-2">
                      <DisabledOverlay disabled={true}>
                        <Button size="sm" variant="outline">Sell</Button>
                      </DisabledOverlay>
                      <DisabledOverlay disabled={true}>
                        <Button size="sm" variant="outline">Transfer</Button>
                      </DisabledOverlay>
                      <DisabledOverlay disabled={true}>
                        <Button size="sm" variant="outline">
                          <FileText className="w-4 h-4" />
                        </Button>
                      </DisabledOverlay>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>

            <div className="mt-6 border rounded-lg p-6">
              <div className="mb-6">
                <h2 className="text-slate-900 mb-1">Position Details</h2>
                <p className="text-slate-600">{position.qty_oz} oz Gold</p>
              </div>

              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div>
                  <p className="text-sm text-slate-600 mb-1">Purchase Price</p>
                  <p className="text-slate-900">${(position.cost_basis_usd / position.qty_oz).toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-600 mb-1">Current Price</p>
                  <p className="text-slate-900">${position.mark_usd.toFixed(2)}</p>
                </div>
                <div>
                  <p className="text-sm text-slate-600 mb-1">Price Change</p>
                  <p className={isProfit ? 'text-green-600' : 'text-red-600'}>
                    {isProfit ? '+' : ''}${(position.mark_usd - (position.cost_basis_usd / position.qty_oz)).toFixed(2)}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-slate-600 mb-1">Change %</p>
                  <p className={isProfit ? 'text-green-600' : 'text-red-600'}>
                    {isProfit ? '+' : ''}{profitLossPercent.toFixed(2)}%
                  </p>
                </div>
              </div>

              <div className="bg-slate-50 p-4 rounded-md">
                <h3 className="text-slate-900 mb-3">Asset Details</h3>
                <div className="space-y-2">
                  <div className="flex items-center gap-2 text-sm">
                    <MapPin className="w-4 h-4 text-slate-600" />
                    <span className="text-slate-600">Vault Location:</span>
                    <span className="text-slate-900">{locationLabel}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Calendar className="w-4 h-4 text-slate-600" />
                    <span className="text-slate-600">Purchase Date:</span>
                    <span className="text-slate-900">{purchaseDate}</span>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <WalletIcon className="w-4 h-4 text-slate-600" />
                    <span className="text-slate-600">Custody:</span>
                    <span className="text-slate-900">Secure Vault Storage</span>
                  </div>
                </div>
              </div>
            </div>

            <div className="mt-6 bg-blue-50 p-4 rounded-md border border-blue-200">
              <h3 className="text-slate-900 mb-2">Live Price Updates</h3>
              <p className="text-sm text-slate-600">
                Prices update every 2 seconds in this demo. In production, you would see real-time market data with historical charts and analytics.
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Actions */}
        <div className="mt-8 flex justify-center">
          <Button variant="outline" onClick={() => navigate('/')}>
            ← Back to Markets
          </Button>
        </div>
      </div>
    </div>
  );
}
