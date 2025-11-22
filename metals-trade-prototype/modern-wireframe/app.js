const fx = { USD: 1, EUR: 0.92, GBP: 0.79, JPY: 157.1 };
const currencySymbols = { USD: '$', EUR: '€', GBP: '£', JPY: '¥' };

const markets = [
  {
    id: 'nymx',
    name: 'NY Mercantile',
    region: 'Americas',
    timezone: 'ET',
    status: 'Open',
    metals: {
      gold: { spot: 2029.4, change: 0.35, bid: 2028.5, ask: 2030.2, high: 2042.1, low: 2018.3 },
      silver: { spot: 24.22, change: -0.04, bid: 24.16, ask: 24.28, high: 24.64, low: 23.92 }
    },
    depth: { gold: 5.4, silver: 3.3 },
    spread: { gold: 1.7, silver: 0.18 }
  },
  {
    id: 'ldn',
    name: 'London Bullion',
    region: 'Europe',
    timezone: 'GMT',
    status: 'Open',
    metals: {
      gold: { spot: 2026.1, change: 0.18, bid: 2025.3, ask: 2026.8, high: 2035.2, low: 2019.5 },
      silver: { spot: 24.04, change: 0.09, bid: 23.98, ask: 24.1, high: 24.31, low: 23.81 }
    },
    depth: { gold: 4.6, silver: 2.8 },
    spread: { gold: 1.5, silver: 0.16 }
  },
  {
    id: 'sgx',
    name: 'Singapore Global Metals',
    region: 'Asia',
    timezone: 'SGT',
    status: 'Pre',
    metals: {
      gold: { spot: 2023.3, change: -0.06, bid: 2022.5, ask: 2023.9, high: 2030.4, low: 2015.1 },
      silver: { spot: 24.31, change: 0.22, bid: 24.24, ask: 24.38, high: 24.68, low: 24.02 }
    },
    depth: { gold: 3.9, silver: 2.6 },
    spread: { gold: 1.8, silver: 0.19 }
  },
  {
    id: 'dgcx',
    name: 'Dubai Commodities',
    region: 'GCC',
    timezone: 'GST',
    status: 'Pre',
    metals: {
      gold: { spot: 2024.8, change: 0.12, bid: 2023.9, ask: 2025.2, high: 2032.5, low: 2017.8 },
      silver: { spot: 23.94, change: -0.02, bid: 23.88, ask: 24.0, high: 24.26, low: 23.76 }
    },
    depth: { gold: 3.6, silver: 2.4 },
    spread: { gold: 1.6, silver: 0.15 }
  }
];

const history = {
  labels: ['-6h', '-5h', '-4h', '-3h', '-2h', '-1h', 'Now'],
  gold: {
    nymx: [2010, 2016, 2020, 2030, 2036, 2032, 2029.4],
    ldn: [2006, 2012, 2018, 2025, 2031, 2028, 2026.1],
    sgx: [2002, 2008, 2016, 2024, 2029, 2025, 2023.3],
    dgcx: [2004, 2011, 2017, 2024, 2030, 2027, 2024.8]
  },
  silver: {
    nymx: [23.74, 23.9, 24.02, 24.3, 24.4, 24.26, 24.22],
    ldn: [23.64, 23.78, 23.92, 24.11, 24.17, 24.1, 24.04],
    sgx: [23.9, 24.05, 24.13, 24.32, 24.35, 24.34, 24.31],
    dgcx: [23.71, 23.86, 23.96, 24.12, 24.18, 24.02, 23.94]
  }
};

let selectedCurrency = 'USD';
let focusMetal = 'gold';
let chart;

const currencySelect = document.getElementById('currency');
const marketGrid = document.getElementById('market-grid');
const ribbon = document.getElementById('ribbon');
const chipRow = document.getElementById('market-chip-row');
const bestRouting = document.getElementById('best-routing');
const orderBest = document.getElementById('order-best');
const orderForm = document.getElementById('order-form');
const orderFeedback = document.getElementById('order-feedback');
const venueSelect = document.getElementById('venue');
const marketEditor = document.getElementById('market-editor');
const chartLabel = document.getElementById('chart-label');
const chartLegend = document.getElementById('chart-legend');

const summaryTargets = {
  gold: {
    price: document.querySelector('[data-target="gold-price"]'),
    delta: document.querySelector('[data-target="gold-delta"]'),
    range: document.querySelector('[data-target="gold-range"]')
  },
  silver: {
    price: document.querySelector('[data-target="silver-price"]'),
    delta: document.querySelector('[data-target="silver-delta"]'),
    range: document.querySelector('[data-target="silver-range"]')
  }
};

const metalButtons = Array.from(document.querySelectorAll('.seg'));
const refreshButton = document.getElementById('refresh');
const shuffleButton = document.getElementById('shuffle');

function convert(value) {
  return value * (fx[selectedCurrency] || 1);
}

function formatMoney(value) {
  const symbol = currencySymbols[selectedCurrency] || '';
  return `${symbol}${value.toFixed(2)}`;
}

function formatChange(value) {
  const sign = value > 0 ? '+' : '';
  return `${sign}${value.toFixed(2)}%`;
}

function averageMetal(metal) {
  const tally = markets.reduce(
    (acc, m) => {
      const stats = m.metals[metal];
      acc.spot += stats.spot;
      acc.change += stats.change;
      acc.high = Math.max(acc.high, stats.high);
      acc.low = Math.min(acc.low, stats.low);
      return acc;
    },
    { spot: 0, change: 0, high: -Infinity, low: Infinity }
  );

  const count = markets.length;
  return {
    spot: tally.spot / count,
    change: tally.change / count,
    high: tally.high,
    low: tally.low
  };
}

function bestVenue(metal) {
  return markets.reduce((best, venue) => {
    const ask = convert(venue.metals[metal].ask);
    if (!best || ask < best.price) {
      return { price: ask, venue };
    }
    return best;
  }, null);
}

function renderSummary() {
  ['gold', 'silver'].forEach((metal) => {
    const summary = averageMetal(metal);
    summaryTargets[metal].price.textContent = formatMoney(convert(summary.spot));
    summaryTargets[metal].delta.textContent = `${formatChange(summary.change)} avg session move`;
    summaryTargets[metal].range.textContent = `Intraday ${formatMoney(convert(summary.low))} - ${formatMoney(convert(summary.high))}`;
  });

  bestRouting.innerHTML = '';
  ['gold', 'silver'].forEach((metal) => {
    const best = bestVenue(metal);
    const chip = document.createElement('div');
    chip.className = 'route-chip';
    chip.innerHTML = `<strong>${metal === 'gold' ? 'Gold' : 'Silver'}</strong> · ${best.venue.name}<br><span class="muted">Best ask ${formatMoney(best.price)}</span>`;
    bestRouting.appendChild(chip);
  });

  const goldBest = bestVenue('gold');
  const silverBest = bestVenue('silver');
  orderBest.textContent = `Gold: ${formatMoney(goldBest.price)} @ ${goldBest.venue.name} • Silver: ${formatMoney(silverBest.price)} @ ${silverBest.venue.name}`;
}

function renderChips() {
  chipRow.innerHTML = '';
  markets.forEach((market) => {
    const chip = document.createElement('div');
    chip.className = 'chip';
    chip.textContent = `${market.name} · ${market.region}`;
    chipRow.appendChild(chip);
  });
}

function renderMarkets() {
  marketGrid.innerHTML = '';
  markets.forEach((market) => {
    if (market.disabled) return;
    const card = document.createElement('div');
    card.className = 'market-card';

    const statusClass = market.status.toLowerCase() === 'open' ? 'open' : 'pre';
    card.innerHTML = `
      <div class="market-header">
        <div>
          <div class="market-name">${market.name}</div>
          <p class="muted">${market.region} · ${market.timezone}</p>
        </div>
        <span class="status ${statusClass}">${market.status === 'Open' ? 'Live' : 'Pre-open'}</span>
      </div>
      <div class="market-body">
        <div class="data-block">
          <p class="label">${focusMetal === 'gold' ? 'Gold' : 'Silver'} ask</p>
          <p class="value">${formatMoney(convert(market.metals[focusMetal].ask))}</p>
          <p class="muted">Bid ${formatMoney(convert(market.metals[focusMetal].bid))}</p>
        </div>
        <div class="data-block">
          <p class="label">Spread</p>
          <p class="value">${market.spread[focusMetal]} pts</p>
          <p class="muted">Depth ${market.depth[focusMetal]}m</p>
        </div>
      </div>
    `;
    marketGrid.appendChild(card);
  });
}

function renderRibbon() {
  ribbon.innerHTML = '';
  markets.forEach((market) => {
    if (market.disabled) return;
    const row = document.createElement('div');
    row.className = 'ribbon-row';
    row.innerHTML = `
      <div>
        <strong>${market.name}</strong>
        <p class="muted">${market.region} · ${market.timezone}</p>
      </div>
      <div>
        <span class="badge">Gold ${formatMoney(convert(market.metals.gold.ask))}</span>
        <span class="badge" style="margin-left:8px;">Silver ${formatMoney(convert(market.metals.silver.ask))}</span>
      </div>
    `;
    ribbon.appendChild(row);
  });
}

function renderChart() {
  const ctx = document.getElementById('priceChart');
  const datasets = markets.filter(m => !m.disabled).map((market) => {
    const color = market.id === 'nymx'
      ? '#5d8dff'
      : market.id === 'ldn'
        ? '#3dd598'
        : market.id === 'sgx'
          ? '#f59f00'
          : '#ff6b6b';
    return {
      label: market.name,
      data: history[focusMetal][market.id].map((v) => convert(v)),
      borderColor: color,
      backgroundColor: color,
      tension: 0.35,
      fill: false
    };
  });

  chartLabel.textContent = `Prices in ${selectedCurrency} baseline`;
  chartLegend.innerHTML = datasets
    .map((d) => `<span><span class="dot" style="background:${d.borderColor}"></span>${d.label}</span>`) 
    .join('');

  if (chart) chart.destroy();
  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: history.labels,
      datasets
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.05)' } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { callback: (v) => formatMoney(v) } }
      }
    }
  });
}

function renderOrderForm() {
  venueSelect.innerHTML = '';
  markets.forEach((market) => {
    if (market.disabled) return;
    const option = document.createElement('option');
    option.value = market.id;
    option.textContent = `${market.name} (${market.region})`;
    venueSelect.appendChild(option);
  });

  const metalSelect = document.getElementById('metal');
  const selectedMetal = metalSelect.value;
  const best = bestVenue(selectedMetal);
  if (best) {
    document.getElementById('limit').placeholder = `Use ${formatMoney(best.price)} ask`;
    venueSelect.value = best.venue.id;
  }
}

function renderMarketEditor() {
  marketEditor.innerHTML = '';
  markets.forEach((market) => {
    const row = document.createElement('div');
    row.className = 'editor-row';
    row.innerHTML = `
      <input value="${market.name}" data-id="${market.id}" aria-label="Edit market name">
      <select data-status="${market.id}">
        <option value="Open" ${market.status === 'Open' ? 'selected' : ''}>Open</option>
        <option value="Pre" ${market.status !== 'Open' ? 'selected' : ''}>Pre</option>
      </select>
      <label class="toggle"><input type="checkbox" data-toggle="${market.id}" ${market.disabled ? '' : 'checked'}> Show on board</label>
    `;
    marketEditor.appendChild(row);

    row.querySelector('input').addEventListener('input', (e) => {
      market.name = e.target.value;
      renderChips();
      renderMarkets();
      renderRibbon();
      renderChart();
      renderOrderForm();
    });

    row.querySelector('select').addEventListener('change', (e) => {
      market.status = e.target.value;
      renderMarkets();
      renderRibbon();
    });

    row.querySelector('input[type="checkbox"]').addEventListener('change', (e) => {
      market.disabled = !e.target.checked;
      renderChips();
      renderMarkets();
      renderRibbon();
      renderChart();
      renderOrderForm();
    });
  });
}

function shuffleSpreads() {
  markets.forEach((market) => {
    market.spread.gold = +(1.3 + Math.random() * 0.8).toFixed(2);
    market.spread.silver = +(0.12 + Math.random() * 0.12).toFixed(2);
    market.metals.gold.ask = +(market.metals.gold.bid + market.spread.gold).toFixed(2);
    market.metals.silver.ask = +(market.metals.silver.bid + market.spread.silver).toFixed(2);
  });
  renderMarkets();
  renderRibbon();
  renderSummary();
  renderChart();
}

function bindEvents() {
  currencySelect.addEventListener('change', (e) => {
    selectedCurrency = e.target.value;
    renderSummary();
    renderMarkets();
    renderRibbon();
    renderChart();
    renderOrderForm();
  });

  metalButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      metalButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      focusMetal = btn.dataset.metal;
      renderMarkets();
      renderChart();
    });
  });

  orderForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const side = document.getElementById('side').value;
    const metal = document.getElementById('metal').value;
    const size = document.getElementById('size').value;
    const venueId = venueSelect.value;
    const venue = markets.find((m) => m.id === venueId);
    const best = bestVenue(metal);
    const suggested = formatMoney(best.price);
    orderFeedback.textContent = `${side === 'buy' ? 'Buying' : 'Selling'} ${size} oz of ${metal} via ${venue.name}. Suggested limit ${suggested}.`;
  });

  refreshButton.addEventListener('click', () => {
    orderFeedback.textContent = 'Tape refreshed with synthetic ticks.';
    shuffleSpreads();
  });

  shuffleButton.addEventListener('click', shuffleSpreads);
}

function init() {
  renderSummary();
  renderChips();
  renderMarkets();
  renderRibbon();
  renderChart();
  renderOrderForm();
  renderMarketEditor();
  bindEvents();
}

document.addEventListener('DOMContentLoaded', init);
