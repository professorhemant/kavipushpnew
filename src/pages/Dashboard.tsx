
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, Package, Calendar, TrendingUp } from 'lucide-react';
import { CsvInventoryService } from '@/services/csvInventoryService';
import { AvailabilityChecker } from '@/components/AvailabilityChecker';

export default function Dashboard() {
  const [stats, setStats] = useState({
    totalCustomers: 0,
    activeBookings: 0,
    inventoryItems: 0
  });

  useEffect(() => {
    const loadStats = () => {
      try {
        // Load customers count
        const savedCustomers = localStorage.getItem('customersData');
        const customersCount = savedCustomers ? JSON.parse(savedCustomers).length : 0;

        // Load active bookings count
        const savedBookings = localStorage.getItem('bookingsData');
        let activeBookingsCount = 0;
        if (savedBookings) {
          const bookings = JSON.parse(savedBookings);
          activeBookingsCount = bookings.filter((booking: any) => 
            booking.status === 'confirmed' || booking.status === 'picked-up'
          ).length;
        }

        // Load inventory items count
        let inventoryCount = 0;
        const csvData = localStorage.getItem('csvInventoryData');
        if (csvData) {
          const inventory = CsvInventoryService.parseCsvToInventory(csvData);
          inventoryCount = inventory.length;
        }

        setStats({
          totalCustomers: customersCount,
          activeBookings: activeBookingsCount,
          inventoryItems: inventoryCount
        });

        console.log('Dashboard stats loaded:', {
          totalCustomers: customersCount,
          activeBookings: activeBookingsCount,
          inventoryItems: inventoryCount
        });
      } catch (error) {
        console.error('Failed to load dashboard stats:', error);
      }
    };

    loadStats();

    // Set up an interval to refresh stats every 30 seconds
    const interval = setInterval(loadStats, 30000);

    return () => clearInterval(interval);
  }, []);

  const dashboardStats = [
    {
      title: "Total Customers",
      value: stats.totalCustomers.toString(),
      change: "",
      icon: Users,
      color: "text-blue-600"
    },
    {
      title: "Active Bookings",
      value: stats.activeBookings.toString(),
      change: "",
      icon: Calendar,
      color: "text-orange-600"
    },
    {
      title: "Inventory Items",
      value: stats.inventoryItems.toString(),
      change: "",
      icon: Package,
      color: "text-purple-600"
    }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold text-primary">Dashboard</h1>
        <p className="text-muted-foreground">Welcome to Kavipushp Jewels Rental Management</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {dashboardStats.map((stat, index) => (
          <Card key={index} className="luxury-shadow">
            <CardContent className="p-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                  <p className="text-2xl font-bold">{stat.value}</p>
                  {stat.change && <p className="text-xs text-muted-foreground mt-1">{stat.change}</p>}
                </div>
                <stat.icon className={`w-8 h-8 ${stat.color}`} />
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Availability Checker */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <AvailabilityChecker />
        
        <Card className="luxury-shadow">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <TrendingUp className="w-5 h-5" />
              Quick Actions
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              <button className="w-full p-3 text-left bg-primary/10 hover:bg-primary/20 rounded-lg transition-colors">
                <div className="flex items-center gap-3">
                  <Users className="w-5 h-5 text-primary" />
                  <div>
                    <p className="font-medium">Add New Customer</p>
                    <p className="text-sm text-muted-foreground">Register a new customer</p>
                  </div>
                </div>
              </button>
              
              <button className="w-full p-3 text-left bg-primary/10 hover:bg-primary/20 rounded-lg transition-colors">
                <div className="flex items-center gap-3">
                  <Calendar className="w-5 h-5 text-primary" />
                  <div>
                    <p className="font-medium">Create Booking</p>
                    <p className="text-sm text-muted-foreground">Book jewelry for a customer</p>
                  </div>
                </div>
              </button>
              
              <button className="w-full p-3 text-left bg-primary/10 hover:bg-primary/20 rounded-lg transition-colors">
                <div className="flex items-center gap-3">
                  <Package className="w-5 h-5 text-primary" />
                  <div>
                    <p className="font-medium">Manage Inventory</p>
                    <p className="text-sm text-muted-foreground">Update jewelry inventory</p>
                  </div>
                </div>
              </button>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Recent Activity */}
      <div className="grid grid-cols-1 gap-6">
        <Card className="luxury-shadow">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="w-5 h-5" />
              Recent Bookings
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-center py-8 text-muted-foreground">
              <Calendar className="w-12 h-12 mx-auto mb-4 opacity-50" />
              <p>No bookings yet</p>
              <p className="text-sm">Recent bookings will appear here</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
