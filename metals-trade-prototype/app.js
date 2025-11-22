const currencyRates = { USD: 1, EUR: 0.92, GBP: 0.8, JPY: 156.4 };
const markets = [
  {
    id: 'nymx',
    name: 'NY Mercantile',
    region: 'Americas',
    status: 'Open',
    timezone: 'ET',
    metals: {
      gold: { spot: 2028.4, change: 0.46, high: 2042.3, low: 2014.2, bid: 2027.6, ask: 2029.1 },
      silver: { spot: 24.18, change: -0.12, high: 24.68, low: 23.96, bid: 24.12, ask: 24.23 }
    },
    spread: { gold: 1.2, silver: 0.16 },
    depth: { gold: 5.4, silver: 3.1 }
  },
  {
    id: 'ldn',
    name: 'London Bullion',
    region: 'Europe',
    status: 'Open',
    timezone: 'GMT',
    metals: {
      gold: { spot: 2025.9, change: 0.38, high: 2036.8, low: 2018.2, bid: 2025.2, ask: 2026.4 },
      silver: { spot: 24.04, change: 0.05, high: 24.35, low: 23.85, bid: 23.98, ask: 24.12 }
    },
    spread: { gold: 1.1, silver: 0.14 },
    depth: { gold: 4.6, silver: 2.7 }
  },
  {
    id: 'sgx',
    name: 'Singapore Global Metals',
    region: 'Asia',
    status: 'Pre',
    timezone: 'SGT',
    metals: {
      gold: { spot: 2021.2, change: -0.08, high: 2031.1, low: 2012.5, bid: 2020.4, ask: 2022.1 },
      silver: { spot: 24.26, change: 0.18, high: 24.54, low: 23.99, bid: 24.18, ask: 24.32 }
    },
    spread: { gold: 1.3, silver: 0.17 },
    depth: { gold: 3.8, silver: 2.9 }
  },
  {
    id: 'dgcx',
    name: 'Dubai Commodities',
    region: 'GCC',
    status: 'Pre',
    timezone: 'GST',
    metals: {
      gold: { spot: 2024.1, change: 0.22, high: 2033.5, low: 2017.7, bid: 2023.2, ask: 2024.6 },
      silver: { spot: 23.92, change: -0.04, high: 24.21, low: 23.68, bid: 23.86, ask: 23.98 }
    },
    spread: { gold: 1.4, silver: 0.15 },
    depth: { gold: 3.9, silver: 2.4 }
  }
];

const marketHistory = {
  labels: ['-6h', '-5h', '-4h', '-3h', '-2h', '-1h', 'Now'],
  gold: {
    nymx: [2009.2, 2011.8, 2016.1, 2023.9, 2032.3, 2028.6, 2028.4],
    ldn: [2006.8, 2010.2, 2015.4, 2021.6, 2028.8, 2026.1, 2025.9],
    sgx: [2002.4, 2006.6, 2012.5, 2020.7, 2026.4, 2022.1, 2021.2],
    dgcx: [2005.3, 2009.5, 2014.7, 2022.4, 2029.1, 2025.2, 2024.1]
  },
  silver: {
    nymx: [23.92, 23.97, 24.12, 24.36, 24.22, 24.2, 24.18],
    ldn: [23.76, 23.88, 24.02, 24.24, 24.16, 24.08, 24.04],
    sgx: [24.02, 24.11, 24.19, 24.36, 24.31, 24.28, 24.26],
    dgcx: [23.81, 23.92, 24.04, 24.21, 24.12, 23.98, 23.92]
  }
};

const watchlist = [
  { metal: 'Gold', venue: 'Spot Pool', bid: 2027.7, ask: 2029.0, trend: 0.42 },
  { metal: 'Gold', venue: 'Options Curve', bid: 2024.2, ask: 2026.5, trend: -0.08 },
  { metal: 'Silver', venue: 'Spot Pool', bid: 24.06, ask: 24.25, trend: 0.12 },
  { metal: 'Silver', venue: 'Futures Strip', bid: 24.11, ask: 24.33, trend: 0.28 }
];

let selectedCurrency = 'USD';
let selectedMetal = 'gold';
let selectedSide = 'buy';
let trendChart;

const currencySelect = document.getElementById('currency');
const marketGridEl = document.getElementById('market-grid');
const marketTagsEl = document.getElementById('market-tags');
const watchTableEl = document.getElementById('watch-table');
const marketRibbonEl = document.getElementById('market-ribbon');
const orderFeedbackEl = document.getElementById('order-feedback');
const venueSelect = document.getElementById('venue');

const chartLabel = document.getElementById('chart-label');
const chartLegend = document.getElementById('chart-legend');

const goldCard = {
  price: document.getElementById('gold-price'),
  delta: document.getElementById('gold-delta'),
  range: document.getElementById('gold-range'),
  depth: document.getElementById('gold-depth')
};
const silverCard = {
  price: document.getElementById('silver-price'),
  delta: document.getElementById('silver-delta'),
  range: document.getElementById('silver-range'),
  depth: document.getElementById('silver-depth')
};

const bestGold = {
  price: document.getElementById('best-gold-price'),
  venue: document.getElementById('best-gold-venue')
};
const bestSilver = {
  price: document.getElementById('best-silver-price'),
  venue: document.getElementById('best-silver-venue')
};

function formatPrice(value) {
  const rate = currencyRates[selectedCurrency] || 1;
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: selectedCurrency }).format(value * rate);
}

function formatDelta(value) {
  const sign = value > 0 ? '+' : '';
  return `${sign}${value.toFixed(2)}%`;
}

function averageMetal(metal) {
  const totals = markets.reduce((acc, m) => {
    const data = m.metals[metal];
    acc.spot += data.spot;
    acc.high = Math.max(acc.high, data.high);
    acc.low = Math.min(acc.low, data.low);
    acc.depth += m.depth[metal];
    acc.change += data.change;
    return acc;
  }, { spot: 0, high: -Infinity, low: Infinity, depth: 0, change: 0 });

  const count = markets.length;
  return {
    spot: totals.spot / count,
    high: totals.high,
    low: totals.low,
    depth: totals.depth,
    change: totals.change / count
  };
}

function renderSummary() {
  const gold = averageMetal('gold');
  goldCard.price.textContent = formatPrice(gold.spot);
  goldCard.delta.textContent = `${formatDelta(gold.change)} vs prior session`;
  goldCard.range.textContent = `Intraday ${formatPrice(gold.low)} - ${formatPrice(gold.high)}`;
  goldCard.depth.textContent = `Visible depth ~ ${gold.depth.toFixed(1)}m`;

  const silver = averageMetal('silver');
  silverCard.price.textContent = formatPrice(silver.spot);
  silverCard.delta.textContent = `${formatDelta(silver.change)} vs prior session`;
  silverCard.range.textContent = `Intraday ${formatPrice(silver.low)} - ${formatPrice(silver.high)}`;
  silverCard.depth.textContent = `Visible depth ~ ${silver.depth.toFixed(1)}m`;
}

function findBestVenue(metal) {
  return markets.reduce((best, venue) => {
    const current = venue.metals[metal];
    if (!best || current.ask < best.price) {
      return { price: current.ask, venueId: venue.id, name: venue.name, region: venue.region, timezone: venue.timezone };
    }
    return best;
  }, null);
}

function renderBestVenues() {
  const goldBest = findBestVenue('gold');
  bestGold.price.textContent = formatPrice(goldBest.price);
  bestGold.venue.textContent = `${goldBest.name} · ${goldBest.region} (${goldBest.timezone})`;
  bestGold.price.dataset.venue = goldBest.venueId;

  const silverBest = findBestVenue('silver');
  bestSilver.price.textContent = formatPrice(silverBest.price);
  bestSilver.venue.textContent = `${silverBest.name} · ${silverBest.region} (${silverBest.timezone})`;
  bestSilver.price.dataset.venue = silverBest.venueId;
}

function renderMarketTags() {
  marketTagsEl.innerHTML = '';
  markets.forEach(m => {
    const tag = document.createElement('div');
    tag.className = 'tag';
    tag.textContent = `${m.name} · ${m.region}`;
    marketTagsEl.appendChild(tag);
  });
}

function renderMarketRibbon() {
  if (!marketRibbonEl) return;
  marketRibbonEl.innerHTML = '';
  markets.forEach(m => {
    const card = document.createElement('div');
    card.className = 'ribbon-card';
    card.innerHTML = `
      <div class="ribbon-head">
        <span class="badge">${m.region}</span>
        <span>${m.name}</span>
      </div>
      <div class="ribbon-body">
        <div>
          <p class="muted label">Gold ask</p>
          <div class="ribbon-price">${formatPrice(m.metals.gold.ask)}</div>
        </div>
        <div>
          <p class="muted label">Silver ask</p>
          <div class="ribbon-price">${formatPrice(m.metals.silver.ask)}</div>
        </div>
      </div>
      <p class="muted">Depth ${m.depth.gold.toFixed(1)}m / ${m.depth.silver.toFixed(1)}m · ${m.timezone}</p>
    `;
    marketRibbonEl.appendChild(card);
  });
}

function renderVenueOptions() {
  venueSelect.innerHTML = '';
  markets.forEach(m => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = `${m.name} (${m.timezone})`;
    venueSelect.appendChild(opt);
  });
}

function renderMarkets() {
  marketGridEl.innerHTML = '';
  markets.forEach(m => {
    const card = document.createElement('div');
    card.className = 'market-card';
    const metalData = m.metals[selectedMetal];
    const altMetal = selectedMetal === 'gold' ? 'silver' : 'gold';
    const altData = m.metals[altMetal];

    card.innerHTML = `
      <header>
        <div>
          <div class="market-name">${m.name}</div>
          <div class="status">${m.region} · ${m.timezone}</div>
        </div>
        <div class="chip">${m.status === 'Open' ? 'Live' : 'Pre-open'}</div>
      </header>
      <div class="metric-row">
        <div>
          <div class="label">${selectedMetal === 'gold' ? 'Gold' : 'Silver'} spot</div>
          <div class="value">${formatPrice(metalData.spot)}</div>
        </div>
        <div class="chip" style="color:${metalData.change >= 0 ? '#7cf0c5' : '#f08f8f'}">${formatDelta(metalData.change)}</div>
      </div>
      <div class="metric-row">
        <span class="label">Spread</span>
        <span class="value">${selectedMetal === 'gold' ? m.spread.gold.toFixed(1) : m.spread.silver.toFixed(2)}</span>
      </div>
      <div class="metric-row">
        <span class="label">Depth</span>
        <span class="value">${m.depth[selectedMetal].toFixed(1)}m</span>
      </div>
      <div class="metric-row">
        <span class="label">Alt: ${altMetal === 'gold' ? 'Gold' : 'Silver'} ${formatPrice(altData.spot)}</span>
        <span class="chip" style="color:${altData.change >= 0 ? '#7cf0c5' : '#f08f8f'}">${formatDelta(altData.change)}</span>
      </div>
    `;

    marketGridEl.appendChild(card);
  });
}

function renderWatchlist() {
  watchTableEl.innerHTML = '';
  const head = document.createElement('div');
  head.className = 'table-row table-head';
  head.innerHTML = '<div>Product</div><div>Venue</div><div>Bid</div><div>Ask</div>';
  watchTableEl.appendChild(head);

  watchlist.forEach(item => {
    const row = document.createElement('div');
    row.className = 'table-row';
    row.innerHTML = `
      <div>${item.metal}</div>
      <div>${item.venue}</div>
      <div>${formatPrice(item.bid)} <span class="muted">(${formatDelta(item.trend)})</span></div>
      <div>${formatPrice(item.ask)}</div>
    `;
    watchTableEl.appendChild(row);
  });
}

function renderChart() {
  const ctx = document.getElementById('trendChart');
  const rate = currencyRates[selectedCurrency] || 1;
  const palette = ['#7cf0c5', '#6ea8ff', '#ffd166', '#f08f8f'];
  const history = marketHistory[selectedMetal];

  if (trendChart) trendChart.destroy();

  const datasets = Object.entries(history).map(([venueId, series], idx) => {
    const venue = markets.find(m => m.id === venueId);
    const color = palette[idx % palette.length];
    return {
      label: `${venue?.name || venueId} (${venue?.timezone || 'GMT'})`,
      data: series.map(v => +(v * rate).toFixed(2)),
      fill: false,
      tension: 0.35,
      borderColor: color,
      backgroundColor: `${color}55`,
      pointRadius: 3,
      borderWidth: 2
    };
  });

  trendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: marketHistory.labels,
      datasets
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#93a0b5' }, grid: { color: 'rgba(255,255,255,0.04)' } },
        y: { ticks: { color: '#93a0b5' }, grid: { color: 'rgba(255,255,255,0.04)' } }
      }
    }
  });

  chartLabel.textContent = `${selectedMetal === 'gold' ? 'Gold' : 'Silver'} | ${selectedCurrency} | Venue overlays`;
  chartLegend.innerHTML = '';
  datasets.forEach(ds => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `<span class="legend-swatch" style="background:${ds.borderColor}"></span>${ds.label}`;
    chartLegend.appendChild(legendItem);
  });
}

function handleCurrencyChange(e) {
  selectedCurrency = e.target.value;
  renderSummary();
  renderMarkets();
  renderWatchlist();
  renderChart();
  renderBestVenues();
  renderMarketRibbon();
}

function handleMetalSwitch(e) {
  const btn = e.target.closest('.seg');
  if (!btn) return;
  document.querySelectorAll('[data-metal]').forEach(el => el.classList.remove('active'));
  btn.classList.add('active');
  selectedMetal = btn.dataset.metal;
  renderMarkets();
  renderChart();
}

function handleSideSwitch(e) {
  const btn = e.target.closest('.seg');
  if (!btn) return;
  document.querySelectorAll('[data-side]').forEach(el => el.classList.remove('active'));
  btn.classList.add('active');
  selectedSide = btn.dataset.side;
}

function routeBestVenue(metal) {
  const best = findBestVenue(metal);
  document.getElementById('metal').value = metal;
  document.getElementById('venue').value = best.venueId;
  selectedSide = 'buy';
  document.querySelectorAll('[data-side]').forEach(el => el.classList.remove('active'));
  document.querySelector('[data-side="buy"]').classList.add('active');

  document.querySelectorAll('[data-metal]').forEach(el => {
    el.classList.toggle('active', el.dataset.metal === metal);
  });
  selectedMetal = metal;
  renderMarkets();
  renderChart();
  renderBestVenues();
  orderFeedbackEl.textContent = `Routing ${metal === 'gold' ? 'XAU' : 'XAG'} buy to ${best.name} at ${formatPrice(best.price)}.`;
}

function simulateQuote() {
  const metal = document.getElementById('metal').value;
  const size = Number(document.getElementById('size').value || 0);
  const venueId = document.getElementById('venue').value;
  const venue = markets.find(m => m.id === venueId) || markets[0];
  const data = venue.metals[metal];
  const price = selectedSide === 'buy' ? data.ask : data.bid;
  const formattedPrice = formatPrice(price);
  orderFeedbackEl.textContent = `${selectedSide === 'buy' ? 'Taking' : 'Hitting'} ${venue.name} at ${formattedPrice} for ${size.toLocaleString()} units.`;
}

function submitOrder() {
  const metal = document.getElementById('metal').value;
  const venueId = document.getElementById('venue').value;
  const venue = markets.find(m => m.id === venueId);
  const limit = document.getElementById('limit').value;
  orderFeedbackEl.textContent = `Preview sent for ${metal.toUpperCase()} ${selectedSide.toUpperCase()} | Venue: ${venue?.name || 'Any'} | Limit: ${limit || 'auto-best'}.`;
}

function editMarkets() {
  const example = markets.map(m => `${m.name} (${m.region})`).join('\n');
  alert(`Prototype action only. Current venues:\n${example}\n\nSwap names or add new markets to test layouts.`);
}

function refreshWatch() {
  watchlist.forEach(item => {
    item.trend = +(item.trend + (Math.random() * 0.2 - 0.1)).toFixed(2);
  });
  renderWatchlist();
}

function init() {
  renderSummary();
  renderMarketTags();
  renderVenueOptions();
  renderMarkets();
  renderWatchlist();
  renderChart();
  renderBestVenues();
  renderMarketRibbon();

  currencySelect.addEventListener('change', handleCurrencyChange);
  document.querySelector('[aria-label="Metal selector"]').addEventListener('click', handleMetalSwitch);
  document.querySelector('[data-side="buy"]').parentElement.addEventListener('click', handleSideSwitch);
  document.getElementById('simulate-quote').addEventListener('click', simulateQuote);
  document.getElementById('submit-order').addEventListener('click', submitOrder);
  document.getElementById('edit-markets').addEventListener('click', editMarkets);
  document.getElementById('refresh-watch').addEventListener('click', refreshWatch);

  document.querySelector('.action-grid').addEventListener('click', (e) => {
    const btn = e.target.closest('[data-best]');
    if (!btn) return;
    routeBestVenue(btn.dataset.best);
  });
}

document.addEventListener('DOMContentLoaded', init);
