import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { DemoRibbon } from './components/DemoRibbon';
import { Landing } from './components/Landing';
import { Compare } from './components/Compare';
import { Buy } from './components/Buy';
import { KYC } from './components/KYC';
import { Payment } from './components/Payment';
import { Confirmation } from './components/Confirmation';
import { Wallet } from './components/Wallet';

export default function App() {
  return (
    <BrowserRouter>
      <div className="min-h-screen">
        <DemoRibbon />
        <Routes>
          <Route path="/" element={<Landing />} />
          <Route path="/compare/gold" element={<Compare />} />
          <Route path="/buy/gold" element={<Buy />} />
          <Route path="/kyc" element={<KYC />} />
          <Route path="/pay" element={<Payment />} />
          <Route path="/confirm" element={<Confirmation />} />
          <Route path="/wallet" element={<Wallet />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </div>
    </BrowserRouter>
  );
}
