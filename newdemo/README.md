# GVEN demo workspace

The screens in `newdemo/` are built with [React](https://react.dev/) and [TypeScript](https://www.typescriptlang.org/).
React components that use JSX plus TypeScript use the `.tsx` file extension. Those files can be run in the browser once they
are bundled by a front-end tool such as [Vite](https://vitejs.dev/).

## Prerequisites

- [Node.js](https://nodejs.org/) v18 or newer
- [pnpm](https://pnpm.io/), [npm](https://www.npmjs.com/), or [yarn](https://yarnpkg.com/) for installing packages (examples below use `npm`).

## Getting started

```bash
cd newdemo
npm install
npm run dev
```

That will start a development server at <http://localhost:5173>. Open that URL in your browser to interact with the demo.

To create a production build run:

```bash
npm run build
npm run preview
```

The preview command serves the production build so you can verify it locally.

## Standalone bundle

If you just want to open the demo in a browser without installing any tooling, use the prebuilt files inside the `dist/`
folder:

1. Build the project once with `npm run build` (or download a copy of the repository that already includes the `dist/`
   directory).
2. Open `dist/index.html` in your browser. All asset paths are relative, so the page will load without running a local
   web server.

You can copy the `dist/` folder to any static hosting service or share it directly as-is; opening the `index.html` file is
all that is required to load the UI.

## Project layout

- `App.tsx` wires together the main routes.
- `components/` contains the demo pages and shared UI pieces.
- `lib/api.ts` holds the mocked backend calls that feed the demo UI.
- `styles/globals.css` defines the design tokens that Tailwind CSS uses for theming.

Feel free to open any of the `.tsx` files in your editor—the TypeScript types provide inline documentation for props and API
responses.
