import { useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { UserCheck, Shield } from 'lucide-react';
import { Button } from './ui/button';
import { Card, CardContent, CardHeader, CardTitle } from './ui/card';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Checkbox } from './ui/checkbox';
import { Badge } from './ui/badge';
import { api } from '../lib/api';

export function KYC() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const nextPath = searchParams.get('next') || '/pay';
  
  const [formData, setFormData] = useState({
    fullName: '',
    email: '',
    addressLine1: '',
    country: 'United States',
    dob: '',
    agreeDemo: false
  });

  const [isSubmitting, setIsSubmitting] = useState(false);

  const isValid = formData.fullName && formData.email && formData.addressLine1 && formData.agreeDemo;

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (isValid) {
      setIsSubmitting(true);
      try {
        const response = await api.submitKYC({
          fullName: formData.fullName,
          email: formData.email,
          address: formData.addressLine1
        });
        
        // Store KYC data in sessionStorage
        sessionStorage.setItem('kycData', JSON.stringify({
          ...formData,
          kyc_id: response.kyc_id,
          user_id: response.user_id
        }));
        
        navigate(nextPath);
      } catch (error) {
        console.error('KYC submission failed:', error);
      } finally {
        setIsSubmitting(false);
      }
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
      <div className="max-w-3xl mx-auto px-4 py-12">
        <div className="mb-8">
          <div className="flex items-center gap-2 mb-2">
            <UserCheck className="w-6 h-6 text-blue-600" />
            <h1 className="text-slate-900">Quick Verification</h1>
          </div>
          <p className="text-slate-600">
            We need a few details to comply with global regulations. This is a simplified demo form.
          </p>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Shield className="w-5 h-5 text-green-600" />
              Account Information
              <Badge variant="outline" className="ml-auto">Demo Mode</Badge>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-6">
              <div>
                <Label htmlFor="kycFullName">Full Name *</Label>
                <Input
                  id="kycFullName"
                  placeholder="John Doe"
                  value={formData.fullName}
                  onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                  className="mt-2"
                  required
                />
              </div>

              <div>
                <Label htmlFor="kycEmail">Email Address *</Label>
                <Input
                  id="kycEmail"
                  type="email"
                  placeholder="john@example.com"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="mt-2"
                  required
                />
              </div>

              <div>
                <Label htmlFor="kycAddressLine1">Address *</Label>
                <Input
                  id="kycAddressLine1"
                  placeholder="123 Main St, City, State, ZIP"
                  value={formData.addressLine1}
                  onChange={(e) => setFormData({ ...formData, addressLine1: e.target.value })}
                  className="mt-2"
                  required
                />
              </div>

              <div>
                <Label htmlFor="kycCountry">Country</Label>
                <div id="kycCountry" className="mt-2 p-3 bg-slate-100 rounded-md">
                  <span className="text-slate-900">United States</span>
                  <p className="text-sm text-slate-600 mt-1">
                    Locked to United States in demo
                  </p>
                </div>
              </div>

              <div>
                <Label htmlFor="kycDOB">Date of Birth (optional in demo)</Label>
                <Input
                  id="kycDOB"
                  type="date"
                  value={formData.dob}
                  onChange={(e) => setFormData({ ...formData, dob: e.target.value })}
                  className="mt-2"
                />
              </div>

              <div className="bg-slate-50 p-4 rounded-md space-y-3">
                <div className="flex items-start space-x-2">
                  <Checkbox
                    id="kycAgree"
                    checked={formData.agreeDemo}
                    onCheckedChange={(checked) => 
                      setFormData({ ...formData, agreeDemo: checked as boolean })
                    }
                  />
                  <Label htmlFor="kycAgree" className="text-sm cursor-pointer">
                    I confirm this is a demo and data is not real. I agree to the Terms of Service and Privacy Policy.
                  </Label>
                </div>
              </div>

              <div className="bg-blue-50 p-4 rounded-md">
                <h3 className="text-slate-900 mb-2 flex items-center gap-2">
                  <Shield className="w-4 h-4" />
                  Data Protection
                </h3>
                <p className="text-sm text-slate-600">
                  In the full application, this would include identity verification, document upload, and compliance checks. Your data would be encrypted and stored securely. GVEN demo is not meant for collecting real PII or securing sensitive data.
                </p>
              </div>

              <div className="flex gap-4">
                <Button
                  type="button"
                  variant="outline"
                  onClick={() => navigate('/buy/gold?market=MILAN&currency=USD')}
                  disabled={isSubmitting}
                >
                  ← Back
                </Button>
                <Button
                  id="btnKycSubmit"
                  type="submit"
                  className="flex-1 bg-blue-600"
                  disabled={!isValid || isSubmitting}
                >
                  {isSubmitting ? 'Verifying...' : 'Continue to Payment'}
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
