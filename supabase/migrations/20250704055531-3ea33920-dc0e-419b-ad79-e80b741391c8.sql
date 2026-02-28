
-- Enable Row Level Security and create policies for the inventory table
-- This will allow the application to read and write inventory data

-- First, let's create policies to allow all operations on the inventory table
-- Since this is inventory data that should be accessible to the application
CREATE POLICY "Allow all operations on inventory" 
ON public.inventory 
FOR ALL 
USING (true) 
WITH CHECK (true);

-- Enable RLS on the inventory table (it might already be enabled)
ALTER TABLE public.inventory ENABLE ROW LEVEL SECURITY;
