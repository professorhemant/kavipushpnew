import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Settings as SettingsIcon, Upload, Database, FileSpreadsheet, CheckCircle, Calculator, TrendingUp } from 'lucide-react';
import { useState, useEffect } from 'react';
import { toast } from '@/hooks/use-toast';
import { PasswordProtection } from '@/components/PasswordProtection';
import { StorageService } from '@/services/storageService';

export default function Settings() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [uploadStatus, setUploadStatus] = useState<string>('');
  const [isProcessing, setIsProcessing] = useState(false);
  const [totalRentEarned, setTotalRentEarned] = useState(0);
  const [completedBookings, setCompletedBookings] = useState(0);
  
  // Business Information State
  const [businessInfo, setBusinessInfo] = useState({
    businessName: '',
    address: '',
    phone: '',
    email: '',
    gstNumber: ''
  });

  // Amount Settings State
  const [amountSettings, setAmountSettings] = useState({
    bookingAmountType: 'tiered' as 'fixed' | 'percentage' | 'tiered',
    fixedBookingAmount: 1000,
    bookingPercentage: 20,
    includeGstInBooking: false,
    securityAmountType: 'tiered' as 'fixed' | 'percentage' | 'tiered',
    fixedSecurityAmount: 5000,
    securityPercentage: 50,
    includeGstInSecurity: false,
    gstRate: 3,
    tier1Max: 3500,
    tier1Amount: 1000,
    tier2Max: 4500,
    tier2Amount: 1500,
    tier3Max: 6500,
    tier3Amount: 2000,
    tier4Amount: 5000,
    securityTier1Max: 3500,
    securityTier1Amount: 2000,
    securityTier2Max: 4500,
    securityTier2Amount: 2500,
    securityTier3Max: 6500,
    securityTier3Amount: 3000,
    securityTier4Amount: 4000
  });

  // Load business info from storage
  useEffect(() => {
    const loadBusinessInfo = () => {
      try {
        const savedInfo = StorageService.getItem('businessInfo');
        if (savedInfo) {
          const parsedInfo = JSON.parse(savedInfo);
          setBusinessInfo(parsedInfo);
          console.log('Loaded business info from storage');
        }
      } catch (error) {
        console.error('Failed to load business info:', error);
      }
    };
    
    loadBusinessInfo();
  }, []);

  // Load amount settings from storage
  useEffect(() => {
    const loadAmountSettings = () => {
      try {
        const savedSettings = StorageService.getItem('amountSettings');
        if (savedSettings) {
          const parsedSettings = JSON.parse(savedSettings);
          setAmountSettings(parsedSettings);
          console.log('Loaded amount settings from storage');
        }
      } catch (error) {
        console.error('Failed to load amount settings:', error);
      }
    };
    
    loadAmountSettings();
  }, []);

  // Calculate rent earnings - runs on every component mount and when data changes
  useEffect(() => {
    const calculateRentEarnings = () => {
      try {
        const savedBookings = StorageService.getItem('bookingsData');
        if (savedBookings) {
          const bookings = JSON.parse(savedBookings);
          
          // Filter for returned bookings (completed rentals)
          const returnedBookings = bookings.filter((booking: any) => booking.status === 'returned');
          
          // Calculate total rent earned from returned bookings
          const totalEarned = returnedBookings.reduce((total: number, booking: any) => {
            return total + (booking.totalAmount || 0);
          }, 0);
          
          setTotalRentEarned(totalEarned);
          setCompletedBookings(returnedBookings.length);
          
          console.log(`Calculated rent earnings: ₹${totalEarned} from ${returnedBookings.length} completed bookings`);
        } else {
          // Reset to 0 if no bookings found
          setTotalRentEarned(0);
          setCompletedBookings(0);
          console.log('No bookings data found, reset earnings to 0');
        }
      } catch (error) {
        console.error('Failed to calculate rent earnings:', error);
        setTotalRentEarned(0);
        setCompletedBookings(0);
      }
    };
    
    calculateRentEarnings();
  }, []);

  const saveBusinessInfo = () => {
    try {
      StorageService.setItem('businessInfo', JSON.stringify(businessInfo));
      toast({
        title: "Business Information Saved",
        description: "Your business information has been saved successfully.",
      });
      console.log('Business info saved to storage');
    } catch (error) {
      console.error('Failed to save business info:', error);
      toast({
        title: "Error",
        description: "Failed to save business information.",
        variant: "destructive",
      });
    }
  };

  const saveAmountSettings = () => {
    try {
      StorageService.setItem('amountSettings', JSON.stringify(amountSettings));
      toast({
        title: "Amount Settings Saved",
        description: "Your amount calculation settings have been saved successfully.",
      });
      console.log('Amount settings saved to storage');
    } catch (error) {
      console.error('Failed to save amount settings:', error);
      toast({
        title: "Error",
        description: "Failed to save amount settings.",
        variant: "destructive",
      });
    }
  };

  const handleCsvUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    setIsProcessing(true);
    setUploadStatus('Processing CSV file...');

    try {
      const text = await file.text();
      
      // Store the raw CSV data
      StorageService.setItem('csvInventoryData', text);
      StorageService.setItem('csvInventoryData_backup', text);
      
      setUploadStatus('CSV file uploaded and saved successfully!');
      
      toast({
        title: "CSV Uploaded",
        description: "Your inventory CSV has been uploaded and saved.",
      });
      
      console.log('CSV file uploaded and saved to storage');
    } catch (error) {
      console.error('Error processing CSV:', error);
      setUploadStatus('Error processing CSV file');
      toast({
        title: "Upload Error",
        description: "There was an error processing your CSV file.",
        variant: "destructive",
      });
    } finally {
      setIsProcessing(false);
      // Reset file input
      if (event.target) {
        event.target.value = '';
      }
    }
  };

  const handleAuthenticated = () => {
    setIsAuthenticated(true);
  };

  return (
    <PasswordProtection onAuthenticated={handleAuthenticated}>
      <div className="space-y-6">
        <div>
          <h1 className="text-3xl font-bold text-primary flex items-center gap-2">
            <SettingsIcon className="w-8 h-8" />
            Settings
          </h1>
          <p className="text-muted-foreground">Configure your application settings and business information</p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Rent Earnings Overview */}
          <Card className="luxury-shadow">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <TrendingUp className="w-5 h-5" />
                Rent Earnings Overview
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div className="bg-green-50 p-4 rounded-lg border border-green-200">
                  <div className="text-2xl font-bold text-green-700">
                    ₹{totalRentEarned.toLocaleString()}
                  </div>
                  <div className="text-sm text-green-600">Total Rent Earned</div>
                </div>
                <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                  <div className="text-2xl font-bold text-blue-700">
                    {completedBookings}
                  </div>
                  <div className="text-sm text-blue-600">Completed Bookings</div>
                </div>
              </div>
              <div className="bg-gray-50 p-3 rounded-md">
                <p className="text-sm text-muted-foreground">
                  <strong>Note:</strong> Earnings are calculated from bookings with "returned" status only.
                </p>
              </div>
            </CardContent>
          </Card>

          {/* Business Information */}
          <Card className="luxury-shadow">
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Database className="w-5 h-5" />
                Business Information
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 gap-4">
                <div>
                  <Label htmlFor="businessName">Business Name</Label>
                  <Input
                    id="businessName"
                    value={businessInfo.businessName}
                    onChange={(e) => setBusinessInfo({ ...businessInfo, businessName: e.target.value })}
                    placeholder="Enter your business name"
                  />
                </div>
                <div>
                  <Label htmlFor="address">Address</Label>
                  <Input
                    id="address"
                    value={businessInfo.address}
                    onChange={(e) => setBusinessInfo({ ...businessInfo, address: e.target.value })}
                    placeholder="Enter your business address"
                  />
                </div>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="phone">Phone</Label>
                    <Input
                      id="phone"
                      value={businessInfo.phone}
                      onChange={(e) => setBusinessInfo({ ...businessInfo, phone: e.target.value })}
                      placeholder="Phone number"
                    />
                  </div>
                  <div>
                    <Label htmlFor="email">Email</Label>
                    <Input
                      id="email"
                      type="email"
                      value={businessInfo.email}
                      onChange={(e) => setBusinessInfo({ ...businessInfo, email: e.target.value })}
                      placeholder="Email address"
                    />
                  </div>
                </div>
                <div>
                  <Label htmlFor="gstNumber">GST Number</Label>
                  <Input
                    id="gstNumber"
                    value={businessInfo.gstNumber}
                    onChange={(e) => setBusinessInfo({ ...businessInfo, gstNumber: e.target.value })}
                    placeholder="GST registration number"
                  />
                </div>
              </div>
              <Button onClick={saveBusinessInfo} className="w-full gold-gradient text-white">
                <CheckCircle className="w-4 h-4 mr-2" />
                Save Business Information
              </Button>
            </CardContent>
          </Card>
        </div>

        {/* CSV Upload */}
        <Card className="luxury-shadow">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <FileSpreadsheet className="w-5 h-5" />
              CSV Inventory Upload
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="border-2 border-dashed border-muted-foreground/25 rounded-lg p-6 text-center">
              <Upload className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
              <div className="space-y-2">
                <p className="text-lg font-medium">Upload Jewelry Inventory CSV</p>
                <p className="text-sm text-muted-foreground">
                  Select a CSV file containing your jewelry inventory data
                </p>
              </div>
              <div className="mt-4">
                <input
                  type="file"
                  accept=".csv"
                  onChange={handleCsvUpload}
                  className="hidden"
                  id="csv-upload"
                  disabled={isProcessing}
                />
                <Button
                  onClick={() => document.getElementById('csv-upload')?.click()}
                  disabled={isProcessing}
                  className="gold-gradient text-white"
                >
                  <Upload className="w-4 h-4 mr-2" />
                  {isProcessing ? 'Processing...' : 'Choose CSV File'}
                </Button>
              </div>
            </div>
            {uploadStatus && (
              <div className={`p-3 rounded-md ${uploadStatus.includes('Error') ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'}`}>
                {uploadStatus}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Amount Calculation Settings */}
        <Card className="luxury-shadow">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calculator className="w-5 h-5" />
              Amount Calculation Settings
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Booking Amount Settings */}
            <div className="space-y-4">
              <h3 className="text-lg font-semibold">Booking Amount Configuration</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="bookingAmountType">Calculation Type</Label>
                  <select
                    id="bookingAmountType"
                    value={amountSettings.bookingAmountType}
                    onChange={(e) => setAmountSettings({ ...amountSettings, bookingAmountType: e.target.value as 'fixed' | 'percentage' | 'tiered' })}
                    className="w-full p-2 border rounded-md"
                  >
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                    <option value="tiered">Tiered</option>
                  </select>
                </div>
                
                {amountSettings.bookingAmountType === 'fixed' && (
                  <div>
                    <Label htmlFor="fixedBookingAmount">Fixed Amount (₹)</Label>
                    <Input
                      id="fixedBookingAmount"
                      type="number"
                      value={amountSettings.fixedBookingAmount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, fixedBookingAmount: Number(e.target.value) })}
                    />
                  </div>
                )}
                
                {amountSettings.bookingAmountType === 'percentage' && (
                  <div>
                    <Label htmlFor="bookingPercentage">Percentage (%)</Label>
                    <Input
                      id="bookingPercentage"
                      type="number"
                      value={amountSettings.bookingPercentage}
                      onChange={(e) => setAmountSettings({ ...amountSettings, bookingPercentage: Number(e.target.value) })}
                    />
                  </div>
                )}
                
                <div className="flex items-center space-x-2">
                  <input
                    type="checkbox"
                    id="includeGstInBooking"
                    checked={amountSettings.includeGstInBooking}
                    onChange={(e) => setAmountSettings({ ...amountSettings, includeGstInBooking: e.target.checked })}
                  />
                  <Label htmlFor="includeGstInBooking">Include GST</Label>
                </div>
              </div>
              
              {amountSettings.bookingAmountType === 'tiered' && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div>
                    <Label>Tier 1 (≤₹{amountSettings.tier1Max})</Label>
                    <Input
                      type="number"
                      value={amountSettings.tier1Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, tier1Amount: Number(e.target.value) })}
                    />
                  </div>
                  <div>
                    <Label>Tier 2 (≤₹{amountSettings.tier2Max})</Label>
                    <Input
                      type="number"
                      value={amountSettings.tier2Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, tier2Amount: Number(e.target.value) })}
                    />
                  </div>
                  <div>
                    <Label>Tier 3 (≤₹{amountSettings.tier3Max})</Label>
                    <Input
                      type="number"
                      value={amountSettings.tier3Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, tier3Amount: Number(e.target.value) })}
                    />
                  </div>
                  <div>
                    <Label>Tier 4 (≥₹10,000)</Label>
                    <Input
                      type="number"
                      value={amountSettings.tier4Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, tier4Amount: Number(e.target.value) })}
                    />
                  </div>
                </div>
              )}
            </div>

            <Separator />

            {/* Security Amount Settings */}
            <div className="space-y-4">
              <h3 className="text-lg font-semibold">Security Amount Configuration</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="securityAmountType">Calculation Type</Label>
                  <select
                    id="securityAmountType"
                    value={amountSettings.securityAmountType}
                    onChange={(e) => setAmountSettings({ ...amountSettings, securityAmountType: e.target.value as 'fixed' | 'percentage' | 'tiered' })}
                    className="w-full p-2 border rounded-md"
                  >
                    <option value="fixed">Fixed Amount</option>
                    <option value="percentage">Percentage</option>
                    <option value="tiered">Tiered</option>
                  </select>
                </div>
                
                {amountSettings.securityAmountType === 'fixed' && (
                  <div>
                    <Label htmlFor="fixedSecurityAmount">Fixed Amount (₹)</Label>
                    <Input
                      id="fixedSecurityAmount"
                      type="number"
                      value={amountSettings.fixedSecurityAmount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, fixedSecurityAmount: Number(e.target.value) })}
                    />
                  </div>
                )}
                
                {amountSettings.securityAmountType === 'percentage' && (
                  <div>
                    <Label htmlFor="securityPercentage">Percentage (%)</Label>
                    <Input
                      id="securityPercentage"
                      type="number"
                      value={amountSettings.securityPercentage}
                      onChange={(e) => setAmountSettings({ ...amountSettings, securityPercentage: Number(e.target.value) })}
                    />
                  </div>
                )}
                
                <div className="flex items-center space-x-2">
                  <input
                    type="checkbox"
                    id="includeGstInSecurity"
                    checked={amountSettings.includeGstInSecurity}
                    onChange={(e) => setAmountSettings({ ...amountSettings, includeGstInSecurity: e.target.checked })}
                  />
                  <Label htmlFor="includeGstInSecurity">Include GST</Label>
                </div>
              </div>
              
              {amountSettings.securityAmountType === 'tiered' && (
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div>
                    <Label>Tier 1 (≤₹{amountSettings.securityTier1Max})</Label>
                    <Input
                      type="number"
                      value={amountSettings.securityTier1Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, securityTier1Amount: Number(e.target.value) })}
                    />
                  </div>
                  <div>
                    <Label>Tier 2 (≤₹{amountSettings.securityTier2Max})</Label>
                    <Input
                      type="number"
                      value={amountSettings.securityTier2Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, securityTier2Amount: Number(e.target.value) })}
                    />
                  </div>
                  <div>
                    <Label>Tier 3 (≤₹{amountSettings.securityTier3Max})</Label>
                    <Input
                      type="number"
                      value={amountSettings.securityTier3Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, securityTier3Amount: Number(e.target.value) })}
                    />
                  </div>
                  <div>
                    <Label>Tier 4 (≥₹10,000)</Label>
                    <Input
                      type="number"
                      value={amountSettings.securityTier4Amount}
                      onChange={(e) => setAmountSettings({ ...amountSettings, securityTier4Amount: Number(e.target.value) })}
                    />
                  </div>
                </div>
              )}
            </div>

            <Separator />

            {/* GST Rate */}
            <div className="space-y-4">
              <h3 className="text-lg font-semibold">GST Configuration</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="gstRate">GST Rate (%)</Label>
                  <Input
                    id="gstRate"
                    type="number"
                    value={amountSettings.gstRate}
                    onChange={(e) => setAmountSettings({ ...amountSettings, gstRate: Number(e.target.value) })}
                  />
                </div>
              </div>
            </div>

            <Button onClick={saveAmountSettings} className="w-full gold-gradient text-white">
              <CheckCircle className="w-4 h-4 mr-2" />
              Save Amount Settings
            </Button>
          </CardContent>
        </Card>
      </div>
    </PasswordProtection>
  );
}
