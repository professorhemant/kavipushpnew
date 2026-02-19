
import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Key, ExternalLink, CheckCircle, XCircle } from 'lucide-react';
import { toast } from '@/hooks/use-toast';
import { googleSheetsService } from '@/services/googleSheetsService';

export function ApiKeySetup() {
  const [apiKey, setApiKey] = useState('');
  const [isConfigured, setIsConfigured] = useState(false);
  const [testResult, setTestResult] = useState<string>('');

  const handleSaveApiKey = () => {
    if (!apiKey.trim()) {
      toast({
        title: "API Key Required",
        description: "Please enter your Google Sheets API key.",
        variant: "destructive",
      });
      return;
    }

    googleSheetsService.setApiKey(apiKey);
    setIsConfigured(true);
    setTestResult('');
    
    toast({
      title: "API Key Configured",
      description: "Google Sheets integration is now active.",
    });
  };

  const handleTestConnection = async () => {
    setTestResult('Testing connection...');
    
    try {
      const data = await googleSheetsService.fetchInventoryData();
      
      if (data.length > 0) {
        setTestResult(`✅ Success! Found ${data.length} items in your inventory.`);
        toast({
          title: "Connection Successful",
          description: `Found ${data.length} items in your inventory.`,
        });
      } else {
        setTestResult('⚠️ Connected but no data found. Check your sheet structure.');
        toast({
          title: "Connected but Empty",
          description: "Connection successful but no inventory data found. Please check your sheet structure.",
          variant: "destructive",
        });
      }
    } catch (error) {
      setTestResult(`❌ Connection failed: ${error}`);
      toast({
        title: "Connection Failed",
        description: "Please check your API key and sheet permissions.",
        variant: "destructive",
      });
    }
  };

  return (
    <Card className="max-w-2xl mx-auto">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Key className="w-5 h-5" />
          Google Sheets API Configuration
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <Alert>
          <AlertDescription>
            To connect to your Google Sheets inventory, you need to:
            <ol className="list-decimal list-inside mt-2 space-y-1">
              <li>Go to <a href="https://console.cloud.google.com/apis/library/sheets.googleapis.com" target="_blank" rel="noopener noreferrer" className="text-primary hover:underline">Google Cloud Console</a></li>
              <li>Enable the Google Sheets API</li>
              <li>Create an API key (unrestricted or restricted to Sheets API)</li>
              <li>Make your sheet publicly readable (Share → Anyone with link → Viewer)</li>
            </ol>
          </AlertDescription>
        </Alert>

        <div className="space-y-2">
          <Label htmlFor="api-key">Google Sheets API Key</Label>
          <Input
            id="api-key"
            type="password"
            placeholder="Enter your Google Sheets API key"
            value={apiKey}
            onChange={(e) => setApiKey(e.target.value)}
          />
        </div>

        <div className="flex gap-2">
          <Button onClick={handleSaveApiKey} disabled={!apiKey.trim()}>
            Save API Key
          </Button>
          {isConfigured && (
            <Button variant="outline" onClick={handleTestConnection}>
              Test Connection
            </Button>
          )}
        </div>

        {testResult && (
          <Alert className={testResult.includes('✅') ? 'border-green-200 bg-green-50' : testResult.includes('❌') ? 'border-red-200 bg-red-50' : 'border-yellow-200 bg-yellow-50'}>
            <AlertDescription className="whitespace-pre-wrap">
              {testResult}
            </AlertDescription>
          </Alert>
        )}

        <div className="text-sm text-muted-foreground space-y-2">
          <p><strong>Your sheet ID:</strong> 19tHsJdcsydCBvYYRR8G4xZO_3Xyth3TE</p>
          <p><strong>Expected columns:</strong> S.No. | ITEMID | Rental Price | Category | DESCRIPTION</p>
          <p><strong>Sheet URL:</strong> <a 
            href="https://docs.google.com/spreadsheets/d/19tHsJdcsydCBvYYRR8G4xZO_3Xyth3TE/edit"
            target="_blank"
            rel="noopener noreferrer"
            className="text-primary hover:underline inline-flex items-center gap-1"
          >
            Open your sheet <ExternalLink className="w-3 h-3" />
          </a></p>
        </div>
      </CardContent>
    </Card>
  );
}
