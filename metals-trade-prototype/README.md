# Metals Desk Prototype (Gold & Silver)

A polished single-page prototype that demonstrates how an institutional trading desk might present gold (XAU) and silver (XAG) side-by-side across multiple markets. The experience is intentionally front-end only: all data is predefined in JavaScript to showcase layout, interactions, and venue-comparison logic without any backend.

## What this prototype shows
- **Dual-universe focus**: Gold and silver are treated as separate books, reinforced by pinned search tokens and segmented controls.
- **USD-first pricing with currency toggle**: Pricing defaults to USD while allowing quick conversion to EUR, GBP, or JPY using in-memory FX rates.
- **Venue contrast at a glance**: Four sample markets (NY Mercantile, London Bullion, Singapore Global Metals, Dubai Commodities) show status, spreads, depth, and timezone context.
- **Best-venue discovery**: Call-to-action cards highlight the lowest available ask per metal and can auto-route the order stencil to that venue.
- **Market ribbon**: A dedicated ribbon surfaces per-market gold and silver asks in USD with depth callouts for fast scanning.
- **Composite stats**: Average spot, change, intraday range, and depth are summarized for each metal.
- **Order stencil (RFQ)**: A structured form for buy/sell, metal, size, venue, and limit with simulated quote/preview responses.
- **Performance chart**: Chart.js overlays per-venue history for the selected metal, emphasizing cross-market divergence on a larger canvas.
- **Watchlist**: Quick metals view with bid/ask and trend markers plus a lightweight refresh to randomize moves.

## Files
- `index.html` — page structure, layout containers, and semantic sections for the board.
- `styles.css` — modern UI styling with gradients, cards, ribbons, and responsive grid behavior.
- `app.js` — all prototype data (markets, history, FX) and rendering logic for cards, chart, watchlist, and form interactions.

## How data and logic work
- **Markets & pricing**: `markets` defines four sample venues with gold/silver spot, bid/ask, change, spreads, depth, status, and timezone details. These values drive every card, tag, and ribbon element.
- **History overlays**: `marketHistory` contains per-venue series for gold and silver so the Chart.js line chart can plot them together on a shared time axis.
- **FX conversion**: `currencyRates` maps USD, EUR, GBP, and JPY to conversion factors; changing the dropdown re-renders all price outputs in the chosen currency.
- **Summaries & best venue**: `averageMetal()` aggregates each metal’s averages to populate the composite cards, while `findBestVenue()` selects the lowest ask to populate the best-venue CTAs and order defaults.
- **Interactions**: UI handlers switch metal/side, refresh watchlist trends, mock market editing, simulate quote/preview text, and route the RFQ form to the cheapest venue.

## Running the prototype
No build steps are required. Open `index.html` in a browser (or serve the `metals-trade-prototype` folder via any static server) and all assets load locally, with Chart.js pulled from a CDN.

## Customizing the demo
- **Add or rename venues**: Update the `markets` array in `app.js` to change venue names, regions, status, spreads, or pricing. The grid, ribbon, best-venue buttons, and RFQ dropdown will update automatically.
- **Adjust pricing history**: Modify `marketHistory` to reflect different movements; the performance chart will re-render with the new series.
- **Change FX rates**: Edit `currencyRates` to test alternative USD conversions or add more currencies and `<option>` values.
- **Tweak UI**: Adjust tokens, hero copy, or layout by editing `index.html` and `styles.css`; the design uses card-based patterns and CSS grids for responsive behavior.
