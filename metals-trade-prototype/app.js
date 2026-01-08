const currencyRates = { USD: 1, EUR: 0.92, GBP: 0.8, JPY: 156.4, SGD: 1.35, AED: 3.67 };
const markets = [
  {
    id: 'nymx',
    name: 'NY Mercantile',
    region: 'Americas',
    status: 'Open',
    timezone: 'ET',
    currency: 'USD',
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
    currency: 'GBP',
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
    currency: 'SGD',
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
    currency: 'AED',
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

const fxHistory = {
  nymx: [1, 1, 1, 1, 1, 1, 1],
  ldn: [0.79, 0.79, 0.8, 0.81, 0.8, 0.8, 0.8],
  sgx: [1.34, 1.35, 1.35, 1.36, 1.35, 1.35, 1.35],
  dgcx: [3.67, 3.67, 3.67, 3.66, 3.67, 3.67, 3.67]
};

const periodOptions = [
  { id: 'day', label: 'Day', labels: marketHistory.labels },
  { id: 'week', label: 'Week', labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] },
  { id: 'month', label: 'Month', labels: ['Wk 1', 'Wk 2', 'Wk 3', 'Wk 4', 'Wk 5', 'Wk 6', 'Wk 7', 'Wk 8'] },
  { id: 'year', label: 'Year', labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'] },
  { id: 'fiveYear', label: '5 Year', labels: ['2019', '2020', '2021', '2022', '2023', '2024'] },
  { id: 'all', label: 'All time', labels: ['2015', '2016', '2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024'] }
];

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
let marketCompareChart;
const selectedMarketIds = new Set(markets.map(m => m.id));
const selectedSeries = new Set(['usd']);
let selectedPeriod = 'day';
const widthStorageKey = 'marketCardWidths';
const ribbonWidthStorageKey = 'marketRibbonWidths';
const marketWidths = {};
const ribbonWidths = {};

const currencySymbols = { USD: '$', EUR: '€', GBP: '£', JPY: '¥', SGD: 'S$', AED: 'د.إ' };

const currencySelect = document.getElementById('currency');
const marketGridEl = document.getElementById('market-grid');
const marketTagsEl = document.getElementById('market-tags');
const watchTableEl = document.getElementById('watch-table');
const marketRibbonEl = document.getElementById('market-ribbon');
const orderFeedbackEl = document.getElementById('order-feedback');
const venueSelect = document.getElementById('venue');
const compareResultsEl = document.getElementById('compare-results');

const chartLabel = document.getElementById('chart-label');
const chartLegend = document.getElementById('chart-legend');
const chartGrowth = document.getElementById('chart-growth');
const compareLegend = document.getElementById('compare-legend');
const compareChartLabel = document.getElementById('compare-chart-label');
const pinnedNote = document.getElementById('pinned-note');
const heroMarketList = document.getElementById('hero-market-list');
const marketFiltersEl = document.getElementById('market-filters');
const seriesFiltersEl = document.getElementById('series-filters');
const timeFiltersEl = document.getElementById('time-filters');
const currencyTokens = {
  gold: document.getElementById('gold-currency-token'),
  silver: document.getElementById('silver-currency-token')
};

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

function formatCurrencyValue(value) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: selectedCurrency }).format(value);
}

function formatCurrencyFor(code, value) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: code }).format(value);
}

function formatNumber(value, decimals = 2) {
  return Number(value).toFixed(decimals);
}

function formatPrice(value) {
  const rate = currencyRates[selectedCurrency] || 1;
  return formatCurrencyValue(value * rate);
}

function currencyLabel(code) {
  const symbol = currencySymbols[code] || '';
  return symbol ? `${code} (${symbol})` : code;
}

function formatFxRate(value) {
  return Number(value).toFixed(3);
}

function formatDelta(value) {
  const sign = value > 0 ? '+' : '';
  return `${sign}${value.toFixed(2)}%`;
}

function buildSeries(baseSeries, points) {
  if (!Array.isArray(baseSeries) || baseSeries.length === 0) return [];
  if (points <= 1) return [baseSeries[0]];
  if (points === baseSeries.length) return [...baseSeries];
  const start = baseSeries[0];
  const end = baseSeries[baseSeries.length - 1];
  const span = end - start;
  return Array.from({ length: points }, (_, idx) => {
    const ratio = idx / (points - 1);
    const curve = Math.sin(ratio * Math.PI) * span * 0.04;
    return +(start + span * ratio + curve).toFixed(2);
  });
}

function getPeriodDefinition() {
  return periodOptions.find(option => option.id === selectedPeriod) || periodOptions[0];
}

function formatGrowthValue(seriesType, delta) {
  if (seriesType === 'fx') return formatFxRate(delta);
  return formatNumber(delta);
}

function formatStatus(status) {
  const normalized = status?.toLowerCase() === 'open' ? 'open' : 'pre';
  return {
    text: normalized === 'open' ? 'Live' : 'Pre-open',
    className: normalized === 'open' ? 'live' : 'pre'
  };
}

function loadStoredWidths(key) {
  try {
    if (typeof localStorage === 'undefined') return {};
    const saved = localStorage.getItem(key);
    const parsed = saved ? JSON.parse(saved) : null;
    if (parsed && typeof parsed === 'object') {
      return parsed;
    }
  } catch (err) {
    console.warn('Unable to read stored widths', err);
  }
  return {};
}

function persistWidths(key, widths) {
  if (typeof localStorage === 'undefined') return;
  localStorage.setItem(key, JSON.stringify(widths));
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

function renderHeroMarketSummary() {
  if (!heroMarketList) return;
  heroMarketList.innerHTML = '';

  markets.forEach(m => {
    const localRate = currencyRates[m.currency] || 1;
    const chip = document.createElement('div');
    chip.className = 'hero-chip';
    chip.innerHTML = `
      <div class="label">${m.name}<span class="badge">${m.region}</span></div>
      <div class="prices">
        <span>Gold USD ${formatCurrencyFor('USD', m.metals.gold.ask)}</span>
        <span>Local ${currencyLabel(m.currency)} ${formatCurrencyFor(m.currency, m.metals.gold.ask * localRate)}</span>
        <span>FX ${m.currency}/USD ${formatFxRate(localRate)}</span>
      </div>
    `;
    heroMarketList.appendChild(chip);
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
    applyStoredWidth(card, ribbonWidths, m.id, 220);
    addResizeHandle(card, { store: ribbonWidths, storageKey: ribbonWidthStorageKey, itemId: m.id });
    marketRibbonEl.appendChild(card);
  });
}

function rankMarkets(metal) {
  const sorted = [...markets].sort((a, b) => a.metals[metal].ask - b.metals[metal].ask);
  const [first, second] = sorted;

  const diff = second.metals[metal].ask - first.metals[metal].ask;
  const spreadNote = `${first.spread[metal].toFixed(2)} vs ${second.spread[metal].toFixed(2)} spread`;
  const reason = `Lowest ask, ${formatPrice(diff)} inside ${second.name} and tighter depth (${spreadNote}).`;

  return {
    first: {
      name: first.name,
      region: first.region,
      ask: formatPrice(first.metals[metal].ask)
    },
    second: {
      name: second.name,
      region: second.region,
      ask: formatPrice(second.metals[metal].ask)
    },
    reason
  };
}

function renderComparisonResults() {
  if (!compareResultsEl) return;
  compareResultsEl.innerHTML = '';

  ['gold'].forEach(metal => {
    const rank = rankMarkets(metal);
    const card = document.createElement('div');
    card.className = 'compare-card';
    card.innerHTML = `
      <header>
        <span>${metal === 'gold' ? 'Gold' : 'Silver'} leaders</span>
        <span class="badge">Best cross-market</span>
      </header>
      <div class="positions">
        <div>
          <span class="label">First</span>
          <strong>${rank.first.name}</strong>
          <span class="muted">${rank.first.region}</span>
          <span>${rank.first.ask}</span>
        </div>
        <div>
          <span class="label">Second</span>
          <strong>${rank.second.name}</strong>
          <span class="muted">${rank.second.region}</span>
          <span>${rank.second.ask}</span>
        </div>
      </div>
      <p class="reason">${rank.reason}</p>
    `;
    compareResultsEl.appendChild(card);
  });
}

function updatePinnedSummary() {
  if (!pinnedNote) return;
  pinnedNote.textContent = `Gold (XAU) pinned from search · ${markets.length} markets · Prices in ${currencyLabel(selectedCurrency)}.`;
  const label = currencyLabel(selectedCurrency);
  if (currencyTokens.gold) currencyTokens.gold.textContent = `Gold tracked in ${label}`;
  if (compareChartLabel) {
    compareChartLabel.textContent = `Prices shown per venue in ${label}`;
  }
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

function applyStoredWidth(card, store, itemId, defaultWidth) {
  const storedWidth = store[itemId];
  const width = storedWidth || defaultWidth;
  card.style.flexBasis = `${width}px`;
  card.style.width = `${width}px`;
}

function startResize(card, store, storageKey, itemId, event) {
  event.preventDefault();
  const handle = event.currentTarget;
  const isPointerEvent = typeof event.pointerId === 'number';
  const moveEvent = isPointerEvent ? 'pointermove' : 'mousemove';
  const upEvent = isPointerEvent ? 'pointerup' : 'mouseup';

  if (isPointerEvent && handle.setPointerCapture) {
    handle.setPointerCapture(event.pointerId);
  }
  const startX = event.clientX;
  const initialWidth = card.getBoundingClientRect().width;
  let currentWidth = initialWidth;
  card.classList.add('resizing');

  const onMove = moveEvent => {
    const delta = moveEvent.clientX - startX;
    currentWidth = Math.max(220, Math.min(520, initialWidth + delta));
    card.style.flexBasis = `${currentWidth}px`;
    card.style.width = `${currentWidth}px`;
  };

  const onUp = () => {
    card.classList.remove('resizing');
    store[itemId] = Math.round(currentWidth);
    persistWidths(storageKey, store);
    window.removeEventListener(moveEvent, onMove);
    window.removeEventListener(upEvent, onUp);
    window.removeEventListener('pointercancel', onUp);
  };

  window.addEventListener(moveEvent, onMove);
  window.addEventListener(upEvent, onUp);
  if (isPointerEvent) {
    window.addEventListener('pointercancel', onUp);
  }
}

function addResizeHandle(card, { store, storageKey, itemId }) {
  const handle = document.createElement('div');
  handle.className = 'resize-handle';
  handle.title = 'Drag to resize';
  handle.addEventListener('pointerdown', event => startResize(card, store, storageKey, itemId, event));
  card.appendChild(handle);
}

function renderMarkets() {
  marketGridEl.innerHTML = '';
  markets.forEach(m => {
    const card = document.createElement('div');
    card.className = 'market-card';
    card.dataset.marketId = m.id;
    const metalData = m.metals[selectedMetal];
    const altMetal = selectedMetal === 'gold' ? 'silver' : 'gold';
    const altData = m.metals[altMetal];
    const statusMeta = formatStatus(m.status);

    card.innerHTML = `
      <header>
        <div>
          <div class="market-name">${m.name}</div>
          <div class="status">${m.region} · ${m.timezone}</div>
        </div>
        <div class="chip ${statusMeta.className}">${statusMeta.text}</div>
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

    applyStoredWidth(card, marketWidths, m.id, 260);
    addResizeHandle(card, { store: marketWidths, storageKey: widthStorageKey, itemId: m.id });
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
  const palette = ['#7cf0c5', '#6ea8ff', '#ffd166', '#f08f8f'];
  const history = marketHistory.gold;
  const periodDefinition = getPeriodDefinition();
  const labels = periodDefinition.labels;
  const seriesTypes = [
    { id: 'usd', label: 'Gold USD', dash: [] },
    { id: 'local', label: 'Gold Local', dash: [6, 4] },
    { id: 'fx', label: 'FX rate', dash: [2, 6] }
  ];
  const primarySeriesType = selectedSeries.has('usd')
    ? 'usd'
    : selectedSeries.has('local')
      ? 'local'
      : 'fx';

  if (trendChart) trendChart.destroy();

  const datasets = Object.entries(history).flatMap(([venueId, series], idx) => {
    if (!selectedMarketIds.has(venueId)) return [];
    const venue = markets.find(m => m.id === venueId);
    const color = palette[idx % palette.length];
    const localRate = currencyRates[venue?.currency] || 1;
    const baseSeries = buildSeries(series, labels.length);
    const baseFxSeries = buildSeries(fxHistory[venueId] || [], labels.length);
    return seriesTypes.flatMap(seriesType => {
      if (!selectedSeries.has(seriesType.id)) return [];
      const data = seriesType.id === 'usd'
        ? baseSeries
        : seriesType.id === 'local'
          ? baseSeries.map(v => +(v * localRate).toFixed(2))
          : baseFxSeries;
      return [{
        label: `${venue?.name || venueId} · ${seriesType.label}`,
        data,
        fill: false,
        tension: 0.35,
        borderColor: color,
        backgroundColor: `${color}55`,
        pointRadius: 2,
        borderWidth: 2,
        borderDash: seriesType.dash,
        yAxisID: seriesType.id === 'fx' ? 'y1' : 'y'
      }];
    });
  });

  trendChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#93a0b5' }, grid: { color: 'rgba(255,255,255,0.04)' } },
        y: {
          ticks: {
            color: '#93a0b5',
            callback: value => formatNumber(value)
          },
          grid: { color: 'rgba(255,255,255,0.04)' }
        },
        y1: {
          position: 'right',
          ticks: {
            color: '#93a0b5',
            callback: value => formatFxRate(value)
          },
          grid: { drawOnChartArea: false }
        }
      }
    }
  });

  const activeSeriesLabels = seriesTypes
    .filter(series => selectedSeries.has(series.id))
    .map(series => series.label)
    .join(', ');
  chartLabel.textContent = `Gold | ${activeSeriesLabels} · ${periodDefinition.label}`;
  chartLegend.innerHTML = '';
  datasets.forEach(ds => {
    const legendItem = document.createElement('div');
    legendItem.className = 'legend-item';
    legendItem.innerHTML = `<span class="legend-swatch" style="background:${ds.borderColor}"></span>${ds.label}`;
    chartLegend.appendChild(legendItem);
  });

  if (chartGrowth) {
    chartGrowth.innerHTML = '';
    const growthTitle = document.createElement('div');
    growthTitle.className = 'growth-title';
    growthTitle.textContent = `Change over ${periodDefinition.label.toLowerCase()}`;
    chartGrowth.appendChild(growthTitle);

    Object.entries(history).forEach(([venueId, series], idx) => {
      if (!selectedMarketIds.has(venueId)) return;
      const venue = markets.find(m => m.id === venueId);
      const localRate = currencyRates[venue?.currency] || 1;
      const baseSeries = buildSeries(series, labels.length);
      const baseFxSeries = buildSeries(fxHistory[venueId] || [], labels.length);
      const data = primarySeriesType === 'usd'
        ? baseSeries
        : primarySeriesType === 'local'
          ? baseSeries.map(v => +(v * localRate).toFixed(2))
          : baseFxSeries;
      const start = data[0];
      const end = data[data.length - 1];
      const delta = +(end - start).toFixed(2);
      const percent = start ? (delta / start) * 100 : 0;
      const sign = delta > 0 ? '+' : '';
      const color = palette[idx % palette.length];
      const item = document.createElement('div');
      item.className = 'growth-item';
      item.innerHTML = `
        <span class="legend-swatch" style="background:${color}"></span>
        <span class="growth-name">${venue?.name || venueId}</span>
        <span class="growth-value">${sign}${formatGrowthValue(primarySeriesType, delta)} (${formatDelta(percent)})</span>
      `;
      chartGrowth.appendChild(item);
    });
  }
}

function renderChartFilters() {
  if (!marketFiltersEl || !seriesFiltersEl || !timeFiltersEl) return;

  marketFiltersEl.innerHTML = '';
  seriesFiltersEl.innerHTML = '';
  timeFiltersEl.innerHTML = '';

  const allMarketsSelected = selectedMarketIds.size === markets.length;
  const allMarketBtn = document.createElement('button');
  allMarketBtn.className = `filter-chip${allMarketsSelected ? ' active' : ''}`;
  allMarketBtn.textContent = 'All';
  allMarketBtn.addEventListener('click', () => {
    selectedMarketIds.clear();
    markets.forEach(m => selectedMarketIds.add(m.id));
    renderChartFilters();
    renderChart();
  });
  marketFiltersEl.appendChild(allMarketBtn);

  markets.forEach(market => {
    const btn = document.createElement('button');
    btn.className = `filter-chip${selectedMarketIds.has(market.id) ? ' active' : ''}`;
    btn.textContent = market.name;
    btn.addEventListener('click', () => {
      if (selectedMarketIds.has(market.id) && selectedMarketIds.size === 1) return;
      if (selectedMarketIds.has(market.id)) {
        selectedMarketIds.delete(market.id);
      } else {
        selectedMarketIds.add(market.id);
      }
      renderChartFilters();
      renderChart();
    });
    marketFiltersEl.appendChild(btn);
  });

  const seriesOptions = [
    { id: 'usd', label: 'Gold USD' },
    { id: 'local', label: 'Gold Local' },
    { id: 'fx', label: 'FX rate' }
  ];

  seriesOptions.forEach(option => {
    const btn = document.createElement('button');
    btn.className = `filter-chip${selectedSeries.has(option.id) ? ' active' : ''}`;
    btn.textContent = option.label;
    btn.addEventListener('click', () => {
      if (selectedSeries.has(option.id) && selectedSeries.size === 1) return;
      if (selectedSeries.has(option.id)) {
        selectedSeries.delete(option.id);
      } else {
        selectedSeries.add(option.id);
      }
      renderChartFilters();
      renderChart();
    });
    seriesFiltersEl.appendChild(btn);
  });

  periodOptions.forEach(option => {
    const btn = document.createElement('button');
    btn.className = `filter-chip${selectedPeriod === option.id ? ' active' : ''}`;
    btn.textContent = option.label;
    btn.addEventListener('click', () => {
      selectedPeriod = option.id;
      renderChartFilters();
      renderChart();
    });
    timeFiltersEl.appendChild(btn);
  });
}

function renderMarketComparisonChart() {
  const ctx = document.getElementById('marketCompareChart');
  if (!ctx) return;

  const rate = currencyRates[selectedCurrency] || 1;
  const labels = markets.map(m => `${m.name} (${m.region})`);
  const goldAsks = markets.map(m => +(m.metals.gold.ask * rate).toFixed(2));
  const silverAsks = markets.map(m => +(m.metals.silver.ask * rate).toFixed(2));

  const datasets = [
    {
      label: 'Gold ask',
      data: goldAsks,
      backgroundColor: 'rgba(124,240,197,0.55)',
      borderColor: '#7cf0c5',
      borderWidth: 2,
      borderRadius: 8
    },
    {
      label: 'Silver ask',
      data: silverAsks,
      backgroundColor: 'rgba(110,168,255,0.5)',
      borderColor: '#6ea8ff',
      borderWidth: 2,
      borderRadius: 8
    }
  ];

  if (marketCompareChart) marketCompareChart.destroy();

  marketCompareChart = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets },
    options: {
      plugins: { legend: { display: false } },
      responsive: true,
      scales: {
        x: { ticks: { color: '#93a0b5' }, grid: { display: false } },
        y: {
          ticks: {
            color: '#93a0b5',
            callback: (value) => formatCurrencyValue(value)
          },
          grid: { color: 'rgba(255,255,255,0.05)' }
        }
      }
    }
  });

  if (compareLegend) {
    compareLegend.innerHTML = '';
    datasets.forEach(ds => {
      const legendItem = document.createElement('div');
      legendItem.className = 'legend-item';
      legendItem.innerHTML = `<span class="legend-swatch" style="background:${ds.borderColor}"></span>${ds.label}`;
      compareLegend.appendChild(legendItem);
    });
  }
}

function handleCurrencyChange(e) {
  selectedCurrency = e.target.value;
  renderSummary();
  renderMarkets();
  renderWatchlist();
  renderChart();
  renderChartFilters();
  renderBestVenues();
  renderMarketRibbon();
  renderMarketComparisonChart();
  renderHeroMarketSummary();
  renderComparisonResults();
  updatePinnedSummary();
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
  if (!size || size < 1) {
    orderFeedbackEl.textContent = 'Enter a valid size before simulating a quote.';
    return;
  }
  if (!venue) {
    orderFeedbackEl.textContent = 'Select a venue to simulate pricing.';
    return;
  }
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
  const limitMsg = limit ? `${limit} (${currencyLabel(selectedCurrency)})` : 'auto-best';
  orderFeedbackEl.textContent = `Preview sent for ${metal.toUpperCase()} ${selectedSide.toUpperCase()} | Venue: ${venue?.name || 'Any'} | Limit: ${limitMsg}.`;
}

function editMarkets() {
  const example = markets.map(m => `${m.name} (${m.region})`).join('\n');
  alert(`Prototype action only. Current venues:\n${example}\n\nSwap names or add new markets to test layouts.`);
}

function refreshWatch() {
  watchlist.forEach(item => {
    const drift = +(Math.random() * 0.2 - 0.1).toFixed(2);
    item.trend = +(item.trend + drift).toFixed(2);

    const mid = (item.bid + item.ask) / 2;
    const move = mid * (drift / 100);
    const precision = item.metal === 'Gold' ? 2 : 3;

    item.bid = +(item.bid + move).toFixed(precision);
    item.ask = +(Math.max(item.bid + (item.metal === 'Gold' ? 0.3 : 0.02), item.ask + move)).toFixed(precision);
  });
  renderWatchlist();
}

function init() {
  Object.assign(marketWidths, loadStoredWidths(widthStorageKey));
  Object.assign(ribbonWidths, loadStoredWidths(ribbonWidthStorageKey));
  renderSummary();
  renderMarketTags();
  renderVenueOptions();
  renderMarkets();
  renderWatchlist();
  renderChart();
  renderChartFilters();
  renderBestVenues();
  renderMarketRibbon();
  renderMarketComparisonChart();
  renderHeroMarketSummary();
  renderComparisonResults();
  updatePinnedSummary();

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
