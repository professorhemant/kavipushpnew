
-- Create customers table
CREATE TABLE public.customers (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name TEXT NOT NULL,
  contact_number TEXT NOT NULL,
  email TEXT,
  address TEXT NOT NULL,
  id_proof_type TEXT,
  id_proof_number TEXT,
  function_date DATE,
  booking_date DATE,
  pickup_date DATE,
  return_date DATE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create bookings table
CREATE TABLE public.bookings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  customer_name TEXT NOT NULL,
  contact_number TEXT NOT NULL,
  address TEXT,
  id_proof_type TEXT,
  id_proof_number TEXT,
  function_date DATE NOT NULL,
  pickup_date DATE NOT NULL,
  return_date DATE NOT NULL,
  status TEXT DEFAULT 'confirmed' CHECK (status IN ('confirmed', 'picked-up', 'returned', 'cancelled')),
  total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  jewelry_items JSONB DEFAULT '[]'::jsonb,
  selected_item_details JSONB DEFAULT '[]'::jsonb,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create invoices table
CREATE TABLE public.invoices (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  invoice_number TEXT NOT NULL UNIQUE,
  customer_name TEXT NOT NULL,
  type TEXT DEFAULT 'booking' CHECK (type IN ('booking', 'pickup', 'return')),
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  date DATE NOT NULL,
  status TEXT DEFAULT 'pending' CHECK (status IN ('paid', 'pending', 'overdue')),
  booking_data JSONB DEFAULT '{}'::jsonb,
  security_amount DECIMAL(10,2) DEFAULT 0,
  booking_amount DECIMAL(10,2) DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Create amount_settings table
CREATE TABLE public.amount_settings (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  booking_amount_type TEXT DEFAULT 'tiered' CHECK (booking_amount_type IN ('fixed', 'percentage', 'tiered')),
  fixed_booking_amount DECIMAL(10,2) DEFAULT 1000,
  booking_percentage DECIMAL(5,2) DEFAULT 20,
  include_gst_in_booking BOOLEAN DEFAULT false,
  security_amount_type TEXT DEFAULT 'tiered' CHECK (security_amount_type IN ('fixed', 'percentage', 'tiered')),
  fixed_security_amount DECIMAL(10,2) DEFAULT 5000,
  security_percentage DECIMAL(5,2) DEFAULT 50,
  include_gst_in_security BOOLEAN DEFAULT false,
  gst_rate DECIMAL(5,2) DEFAULT 3,
  tier1_max DECIMAL(10,2) DEFAULT 3500,
  tier1_amount DECIMAL(10,2) DEFAULT 1000,
  tier2_max DECIMAL(10,2) DEFAULT 4500,
  tier2_amount DECIMAL(10,2) DEFAULT 1500,
  tier3_max DECIMAL(10,2) DEFAULT 6500,
  tier3_amount DECIMAL(10,2) DEFAULT 2000,
  tier4_amount DECIMAL(10,2) DEFAULT 5000,
  security_tier1_max DECIMAL(10,2) DEFAULT 3500,
  security_tier1_amount DECIMAL(10,2) DEFAULT 2000,
  security_tier2_max DECIMAL(10,2) DEFAULT 4500,
  security_tier2_amount DECIMAL(10,2) DEFAULT 2500,
  security_tier3_max DECIMAL(10,2) DEFAULT 6500,
  security_tier3_amount DECIMAL(10,2) DEFAULT 3000,
  security_tier4_amount DECIMAL(10,2) DEFAULT 4000,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT now(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT now()
);

-- Enable Row Level Security (for future auth implementation)
ALTER TABLE public.customers ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.bookings ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.invoices ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.amount_settings ENABLE ROW LEVEL SECURITY;

-- Create permissive policies for now (you can restrict these later with authentication)
CREATE POLICY "Allow all operations on customers" ON public.customers FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "Allow all operations on bookings" ON public.bookings FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "Allow all operations on invoices" ON public.invoices FOR ALL USING (true) WITH CHECK (true);
CREATE POLICY "Allow all operations on amount_settings" ON public.amount_settings FOR ALL USING (true) WITH CHECK (true);

-- Enable real-time for all tables
ALTER TABLE public.customers REPLICA IDENTITY FULL;
ALTER TABLE public.bookings REPLICA IDENTITY FULL;
ALTER TABLE public.invoices REPLICA IDENTITY FULL;
ALTER TABLE public.amount_settings REPLICA IDENTITY FULL;

-- Add tables to realtime publication
ALTER PUBLICATION supabase_realtime ADD TABLE public.customers;
ALTER PUBLICATION supabase_realtime ADD TABLE public.bookings;
ALTER PUBLICATION supabase_realtime ADD TABLE public.invoices;
ALTER PUBLICATION supabase_realtime ADD TABLE public.amount_settings;

-- Insert default amount settings
INSERT INTO public.amount_settings (
  booking_amount_type, fixed_booking_amount, booking_percentage, include_gst_in_booking,
  security_amount_type, fixed_security_amount, security_percentage, include_gst_in_security,
  gst_rate, tier1_max, tier1_amount, tier2_max, tier2_amount, tier3_max, tier3_amount, tier4_amount,
  security_tier1_max, security_tier1_amount, security_tier2_max, security_tier2_amount, 
  security_tier3_max, security_tier3_amount, security_tier4_amount
) VALUES (
  'tiered', 1000, 20, false,
  'tiered', 5000, 50, false,
  3, 3500, 1000, 4500, 1500, 6500, 2000, 5000,
  3500, 2000, 4500, 2500, 6500, 3000, 4000
);
