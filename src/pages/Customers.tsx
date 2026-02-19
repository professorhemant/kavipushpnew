
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { CustomerForm, Customer } from '@/components/CustomerForm';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Users, Plus, FileSpreadsheet, Search, Trash2, AlertTriangle, Info } from 'lucide-react';
import { Input } from '@/components/ui/input';
import { toast } from '@/hooks/use-toast';
import * as XLSX from 'xlsx';
import { StorageService } from '@/services/storageService';
import { DataBackup } from '@/components/DataBackup';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

// Update Customer interface to include createdAt
interface CustomerWithTimestamp extends Customer {
  createdAt: Date;
}

export default function Customers() {
  const [customers, setCustomers] = useState<CustomerWithTimestamp[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  // Load customers from storage on component mount
  useEffect(() => {
    const loadCustomers = () => {
      console.log('=== LOADING CUSTOMERS ===');
      console.log('Component mount timestamp:', new Date().toISOString());
      
      // Log complete storage status
      StorageService.logStorageStatus();
      
      try {
        const savedCustomers = StorageService.getItem('customersData');
        if (savedCustomers) {
          console.log('Found customers data, parsing...');
          const parsedCustomers = JSON.parse(savedCustomers);
          console.log('Parsed customers count:', parsedCustomers.length);
          
          // Convert date strings back to Date objects and add createdAt if missing
          const customersWithDates = parsedCustomers.map((customer: any) => ({
            ...customer,
            idProofType: customer.idProofType || '',
            idProofNumber: customer.idProofNumber || '',
            functionDate: customer.functionDate ? new Date(customer.functionDate) : null,
            bookingDate: customer.bookingDate ? new Date(customer.bookingDate) : null,
            pickupDate: customer.pickupDate ? new Date(customer.pickupDate) : null,
            returnDate: customer.returnDate ? new Date(customer.returnDate) : null,
            createdAt: customer.createdAt ? new Date(customer.createdAt) : new Date(),
          }));
          
          // Sort by createdAt (latest first)
          customersWithDates.sort((a: CustomerWithTimestamp, b: CustomerWithTimestamp) => 
            b.createdAt.getTime() - a.createdAt.getTime()
          );
          
          setCustomers(customersWithDates);
          console.log(`✅ Successfully loaded ${customersWithDates.length} customers`);
        } else {
          console.log('⚠️ No customers found in storage');
          console.log('Checking what keys are available...');
          const storageInfo = StorageService.getStorageInfo();
          console.log('Available storage keys:', storageInfo.keys);
        }
      } catch (error) {
        console.error('❌ Failed to load customers:', error);
        console.log('Error type:', error.constructor.name);
        console.log('Error message:', error.message);
        
        toast({
          title: "Error Loading Customers",
          description: "There was an issue loading your customer data. Please check the console for details.",
          variant: "destructive",
        });
      }
    };
    
    loadCustomers();
  }, []);

  // Save customers to storage whenever customers change
  useEffect(() => {
    if (customers.length === 0) {
      console.log('Skipping save - no customers to save');
      return;
    }
    
    console.log('=== SAVING CUSTOMERS ===');
    console.log('Customers to save:', customers.length);
    console.log('Save timestamp:', new Date().toISOString());
    
    try {
      const success = StorageService.setItem('customersData', JSON.stringify(customers));
      if (success) {
        console.log(`✅ Successfully saved ${customers.length} customers`);
      } else {
        console.error('❌ Failed to save customers');
        toast({
          title: "Save Error",
          description: "Failed to save customer data. Please export your data as backup.",
          variant: "destructive",
        });
      }
    } catch (error) {
      console.error('❌ Error saving customers:', error);
      toast({
        title: "Save Error", 
        description: "Critical error saving customer data. Please export immediately.",
        variant: "destructive",
      });
    }
  }, [customers]);

  const handleAddCustomer = (customerData: Omit<Customer, 'id'>) => {
    console.log('=== ADDING NEW CUSTOMER ===');
    console.log('Customer name:', customerData.name);
    
    const newCustomer: CustomerWithTimestamp = {
      ...customerData,
      id: Date.now().toString(),
      createdAt: new Date(),
    };
    
    // Add new customer at the beginning (latest first)
    const updatedCustomers = [newCustomer, ...customers];
    setCustomers(updatedCustomers);
    setShowForm(false);
    
    console.log('New customer added, total count:', updatedCustomers.length);
    
    toast({
      title: "Customer Added",
      description: `${customerData.name} has been added successfully.`,
    });
  };

  const handleDeleteCustomer = (customerId: string) => {
    const customerToDelete = customers.find(c => c.id === customerId);
    const updatedCustomers = customers.filter(customer => customer.id !== customerId);
    setCustomers(updatedCustomers);
    
    console.log('Customer deleted:', customerToDelete?.name);
    console.log('Remaining customers:', updatedCustomers.length);
    
    toast({
      title: "Customer Deleted",
      description: `${customerToDelete?.name} has been successfully deleted.`,
    });
  };

  // Filter customers based on search term
  const filteredCustomers = customers.filter(customer =>
    customer.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    customer.contactNumber.includes(searchTerm) ||
    customer.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
    customer.address.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const formatDate = (date: Date | null) => {
    if (!date) return '-';
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const formatDateOnly = (date: Date | null) => {
    if (!date) return '-';
    return date.toLocaleDateString();
  };

  const exportToExcel = () => {
    if (customers.length === 0) {
      toast({
        title: "No Data to Export",
        description: "Please add some customers before exporting.",
        variant: "destructive",
      });
      return;
    }

    // Prepare data for export
    const exportData = customers.map((customer) => ({
      'Customer Name': customer.name,
      'Contact Number': customer.contactNumber,
      'Email': customer.email || '',
      'Address': customer.address,
      'ID Proof Type': customer.idProofType || '',
      'ID Proof Number': customer.idProofNumber || '',
      'Function Date': customer.functionDate ? customer.functionDate.toDateString() : '',
      'Booking Date': customer.bookingDate ? customer.bookingDate.toDateString() : '',
      'Pickup Date': customer.pickupDate ? customer.pickupDate.toDateString() : '',
      'Return Date': customer.returnDate ? customer.returnDate.toDateString() : '',
    }));

    // Create workbook and worksheet
    const ws = XLSX.utils.json_to_sheet(exportData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Customers');

    // Auto-size columns
    const range = XLSX.utils.decode_range(ws['!ref'] || 'A1');
    const colWidths = [];
    for (let C = range.s.c; C <= range.e.c; ++C) {
      let maxWidth = 10;
      for (let R = range.s.r; R <= range.e.r; ++R) {
        const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
        const cell = ws[cellAddress];
        if (cell && cell.v) {
          const cellLength = cell.v.toString().length;
          if (cellLength > maxWidth) {
            maxWidth = cellLength;
          }
        }
      }
      colWidths.push({ width: Math.min(maxWidth + 2, 50) });
    }
    ws['!cols'] = colWidths;

    // Generate filename with current date
    const currentDate = new Date().toISOString().split('T')[0];
    const filename = `customers_${currentDate}.xlsx`;

    // Download file
    XLSX.writeFile(wb, filename);

    toast({
      title: "Excel Export Successful",
      description: `Customer data exported to ${filename}`,
    });

    console.log(`Exported ${customers.length} customers to Excel file: ${filename}`);
  };

  const storageInfo = StorageService.getStorageInfo();
  const hasStorageIssues = storageInfo.type !== 'localStorage' || storageInfo.localStorageSize === 0;

  return (
    <div className="space-y-6">
      {/* Data Backup Component - Always show at top */}
      <DataBackup />

      {hasStorageIssues && (
        <Card className="border-amber-200 bg-amber-50">
          <CardContent className="pt-6">
            <div className="flex items-start gap-3">
              <AlertTriangle className="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
              <div className="text-sm">
                <p className="font-medium text-amber-800">Storage Warning</p>
                <p className="text-amber-700 mt-1">
                  Your data may not persist after refresh. Current storage: {storageInfo.type}
                  <br />• localStorage items: {storageInfo.localStorageSize}
                  <br />• Fallback items: {storageInfo.fallbackSize}
                  <br />• Consider exporting your data regularly as backup
                </p>
                <Button 
                  variant="outline" 
                  size="sm" 
                  className="mt-2"
                  onClick={() => StorageService.logStorageStatus()}
                >
                  <Info className="w-4 h-4 mr-2" />
                  Debug Storage
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold text-primary">Customer Management</h1>
          <p className="text-muted-foreground">Manage your customer database</p>
        </div>
        <div className="flex gap-2">
          <Button 
            onClick={exportToExcel}
            variant="outline"
            className="text-green-600 border-green-600 hover:bg-green-50"
          >
            <FileSpreadsheet className="w-4 h-4 mr-2" />
            Export to Excel
          </Button>
          <Button 
            onClick={() => setShowForm(!showForm)}
            className="gold-gradient text-white"
          >
            <Plus className="w-4 h-4 mr-2" />
            Add Customer
          </Button>
        </div>
      </div>

      {showForm && (
        <CustomerForm onSubmit={handleAddCustomer} />
      )}

      <Card className="luxury-shadow">
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Customer List ({filteredCustomers.length})
              {storageInfo.type !== 'localStorage' && (
                <span className="text-xs text-amber-600">({storageInfo.type})</span>
              )}
            </CardTitle>
            <div className="relative max-w-sm">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
              <Input
                placeholder="Search customers..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pl-10"
              />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          {filteredCustomers.length === 0 ? (
            <div className="text-center py-8">
              <Users className="w-12 h-12 text-muted-foreground mx-auto mb-4" />
              <p className="text-muted-foreground">
                {searchTerm ? 'No customers match your search.' : 'No customers added yet.'}
              </p>
              {!searchTerm && (
                <p className="text-sm text-muted-foreground">Click "Add Customer" to get started.</p>
              )}
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Contact</TableHead>
                    <TableHead>Email</TableHead>
                    <TableHead>ID Proof</TableHead>
                    <TableHead>Function Date</TableHead>
                    <TableHead>Booking Date</TableHead>
                    <TableHead>Added On</TableHead>
                    <TableHead>Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredCustomers.map((customer) => (
                    <TableRow key={customer.id} className="hover:bg-muted/50">
                      <TableCell className="font-medium">
                        <div>
                          <div className="font-semibold">{customer.name}</div>
                          {customer.address && (
                            <div className="text-xs text-muted-foreground truncate max-w-[200px]">
                              {customer.address}
                            </div>
                          )}
                        </div>
                      </TableCell>
                      <TableCell>{customer.contactNumber}</TableCell>
                      <TableCell className="max-w-[150px] truncate">
                        {customer.email || '-'}
                      </TableCell>
                      <TableCell>
                        {customer.idProofType ? (
                          <div className="text-sm">
                            <div className="font-medium">{customer.idProofType}</div>
                            <div className="text-xs text-muted-foreground">
                              {customer.idProofNumber}
                            </div>
                          </div>
                        ) : '-'}
                      </TableCell>
                      <TableCell className="text-sm">
                        {formatDateOnly(customer.functionDate)}
                      </TableCell>
                      <TableCell className="text-sm">
                        {formatDateOnly(customer.bookingDate)}
                      </TableCell>
                      <TableCell className="text-sm text-muted-foreground">
                        {formatDate(customer.createdAt)}
                      </TableCell>
                      <TableCell>
                        <AlertDialog>
                          <AlertDialogTrigger asChild>
                            <Button 
                              variant="outline" 
                              size="sm"
                              className="text-red-600 hover:text-red-700 hover:bg-red-50"
                            >
                              <Trash2 className="w-4 h-4" />
                            </Button>
                          </AlertDialogTrigger>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>Delete Customer</AlertDialogTitle>
                              <AlertDialogDescription>
                                Are you sure you want to delete {customer.name}? 
                                This action cannot be undone.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Cancel</AlertDialogCancel>
                              <AlertDialogAction 
                                onClick={() => handleDeleteCustomer(customer.id)}
                                className="bg-red-600 hover:bg-red-700"
                              >
                                Delete
                              </AlertDialogAction>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
