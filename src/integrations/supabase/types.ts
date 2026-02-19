export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[]

export type Database = {
  public: {
    Tables: {
      amount_settings: {
        Row: {
          booking_amount_type: string | null
          booking_percentage: number | null
          created_at: string | null
          fixed_booking_amount: number | null
          fixed_security_amount: number | null
          gst_rate: number | null
          id: string
          include_gst_in_booking: boolean | null
          include_gst_in_security: boolean | null
          security_amount_type: string | null
          security_percentage: number | null
          security_tier1_amount: number | null
          security_tier1_max: number | null
          security_tier2_amount: number | null
          security_tier2_max: number | null
          security_tier3_amount: number | null
          security_tier3_max: number | null
          security_tier4_amount: number | null
          tier1_amount: number | null
          tier1_max: number | null
          tier2_amount: number | null
          tier2_max: number | null
          tier3_amount: number | null
          tier3_max: number | null
          tier4_amount: number | null
          updated_at: string | null
        }
        Insert: {
          booking_amount_type?: string | null
          booking_percentage?: number | null
          created_at?: string | null
          fixed_booking_amount?: number | null
          fixed_security_amount?: number | null
          gst_rate?: number | null
          id?: string
          include_gst_in_booking?: boolean | null
          include_gst_in_security?: boolean | null
          security_amount_type?: string | null
          security_percentage?: number | null
          security_tier1_amount?: number | null
          security_tier1_max?: number | null
          security_tier2_amount?: number | null
          security_tier2_max?: number | null
          security_tier3_amount?: number | null
          security_tier3_max?: number | null
          security_tier4_amount?: number | null
          tier1_amount?: number | null
          tier1_max?: number | null
          tier2_amount?: number | null
          tier2_max?: number | null
          tier3_amount?: number | null
          tier3_max?: number | null
          tier4_amount?: number | null
          updated_at?: string | null
        }
        Update: {
          booking_amount_type?: string | null
          booking_percentage?: number | null
          created_at?: string | null
          fixed_booking_amount?: number | null
          fixed_security_amount?: number | null
          gst_rate?: number | null
          id?: string
          include_gst_in_booking?: boolean | null
          include_gst_in_security?: boolean | null
          security_amount_type?: string | null
          security_percentage?: number | null
          security_tier1_amount?: number | null
          security_tier1_max?: number | null
          security_tier2_amount?: number | null
          security_tier2_max?: number | null
          security_tier3_amount?: number | null
          security_tier3_max?: number | null
          security_tier4_amount?: number | null
          tier1_amount?: number | null
          tier1_max?: number | null
          tier2_amount?: number | null
          tier2_max?: number | null
          tier3_amount?: number | null
          tier3_max?: number | null
          tier4_amount?: number | null
          updated_at?: string | null
        }
        Relationships: []
      }
      bookings: {
        Row: {
          address: string | null
          contact_number: string
          created_at: string | null
          customer_name: string
          function_date: string
          id: string
          id_proof_number: string | null
          id_proof_type: string | null
          jewelry_items: Json | null
          pickup_date: string
          return_date: string
          selected_item_details: Json | null
          status: string | null
          total_amount: number
          updated_at: string | null
        }
        Insert: {
          address?: string | null
          contact_number: string
          created_at?: string | null
          customer_name: string
          function_date: string
          id?: string
          id_proof_number?: string | null
          id_proof_type?: string | null
          jewelry_items?: Json | null
          pickup_date: string
          return_date: string
          selected_item_details?: Json | null
          status?: string | null
          total_amount?: number
          updated_at?: string | null
        }
        Update: {
          address?: string | null
          contact_number?: string
          created_at?: string | null
          customer_name?: string
          function_date?: string
          id?: string
          id_proof_number?: string | null
          id_proof_type?: string | null
          jewelry_items?: Json | null
          pickup_date?: string
          return_date?: string
          selected_item_details?: Json | null
          status?: string | null
          total_amount?: number
          updated_at?: string | null
        }
        Relationships: []
      }
      customers: {
        Row: {
          address: string
          booking_date: string | null
          contact_number: string
          created_at: string | null
          email: string | null
          function_date: string | null
          id: string
          id_proof_number: string | null
          id_proof_type: string | null
          name: string
          pickup_date: string | null
          return_date: string | null
          updated_at: string | null
        }
        Insert: {
          address: string
          booking_date?: string | null
          contact_number: string
          created_at?: string | null
          email?: string | null
          function_date?: string | null
          id?: string
          id_proof_number?: string | null
          id_proof_type?: string | null
          name: string
          pickup_date?: string | null
          return_date?: string | null
          updated_at?: string | null
        }
        Update: {
          address?: string
          booking_date?: string | null
          contact_number?: string
          created_at?: string | null
          email?: string | null
          function_date?: string | null
          id?: string
          id_proof_number?: string | null
          id_proof_type?: string | null
          name?: string
          pickup_date?: string | null
          return_date?: string | null
          updated_at?: string | null
        }
        Relationships: []
      }
      inventory: {
        Row: {
          CATEGORY: string | null
          DESCRIPTION: string | null
          ITEMID: string | null
          RENTALPRICE: number | null
        }
        Insert: {
          CATEGORY?: string | null
          DESCRIPTION?: string | null
          ITEMID?: string | null
          RENTALPRICE?: number | null
        }
        Update: {
          CATEGORY?: string | null
          DESCRIPTION?: string | null
          ITEMID?: string | null
          RENTALPRICE?: number | null
        }
        Relationships: []
      }
      Inventory: {
        Row: {
          Category: number | null
          Description: number | null
          itemid: number
          Rent: number | null
        }
        Insert: {
          Category?: number | null
          Description?: number | null
          itemid?: number
          Rent?: number | null
        }
        Update: {
          Category?: number | null
          Description?: number | null
          itemid?: number
          Rent?: number | null
        }
        Relationships: []
      }
      invoices: {
        Row: {
          amount: number
          booking_amount: number | null
          booking_data: Json | null
          created_at: string | null
          customer_name: string
          date: string
          id: string
          invoice_number: string
          security_amount: number | null
          status: string | null
          type: string | null
          updated_at: string | null
        }
        Insert: {
          amount?: number
          booking_amount?: number | null
          booking_data?: Json | null
          created_at?: string | null
          customer_name: string
          date: string
          id?: string
          invoice_number: string
          security_amount?: number | null
          status?: string | null
          type?: string | null
          updated_at?: string | null
        }
        Update: {
          amount?: number
          booking_amount?: number | null
          booking_data?: Json | null
          created_at?: string | null
          customer_name?: string
          date?: string
          id?: string
          invoice_number?: string
          security_amount?: number | null
          status?: string | null
          type?: string | null
          updated_at?: string | null
        }
        Relationships: []
      }
    }
    Views: {
      [_ in never]: never
    }
    Functions: {
      [_ in never]: never
    }
    Enums: {
      [_ in never]: never
    }
    CompositeTypes: {
      [_ in never]: never
    }
  }
}

type DefaultSchema = Database[Extract<keyof Database, "public">]

export type Tables<
  DefaultSchemaTableNameOrOptions extends
    | keyof (DefaultSchema["Tables"] & DefaultSchema["Views"])
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof (Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
        Database[DefaultSchemaTableNameOrOptions["schema"]]["Views"])
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? (Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"] &
      Database[DefaultSchemaTableNameOrOptions["schema"]]["Views"])[TableName] extends {
      Row: infer R
    }
    ? R
    : never
  : DefaultSchemaTableNameOrOptions extends keyof (DefaultSchema["Tables"] &
        DefaultSchema["Views"])
    ? (DefaultSchema["Tables"] &
        DefaultSchema["Views"])[DefaultSchemaTableNameOrOptions] extends {
        Row: infer R
      }
      ? R
      : never
    : never

export type TablesInsert<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Insert: infer I
    }
    ? I
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Insert: infer I
      }
      ? I
      : never
    : never

export type TablesUpdate<
  DefaultSchemaTableNameOrOptions extends
    | keyof DefaultSchema["Tables"]
    | { schema: keyof Database },
  TableName extends DefaultSchemaTableNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"]
    : never = never,
> = DefaultSchemaTableNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaTableNameOrOptions["schema"]]["Tables"][TableName] extends {
      Update: infer U
    }
    ? U
    : never
  : DefaultSchemaTableNameOrOptions extends keyof DefaultSchema["Tables"]
    ? DefaultSchema["Tables"][DefaultSchemaTableNameOrOptions] extends {
        Update: infer U
      }
      ? U
      : never
    : never

export type Enums<
  DefaultSchemaEnumNameOrOptions extends
    | keyof DefaultSchema["Enums"]
    | { schema: keyof Database },
  EnumName extends DefaultSchemaEnumNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"]
    : never = never,
> = DefaultSchemaEnumNameOrOptions extends { schema: keyof Database }
  ? Database[DefaultSchemaEnumNameOrOptions["schema"]]["Enums"][EnumName]
  : DefaultSchemaEnumNameOrOptions extends keyof DefaultSchema["Enums"]
    ? DefaultSchema["Enums"][DefaultSchemaEnumNameOrOptions]
    : never

export type CompositeTypes<
  PublicCompositeTypeNameOrOptions extends
    | keyof DefaultSchema["CompositeTypes"]
    | { schema: keyof Database },
  CompositeTypeName extends PublicCompositeTypeNameOrOptions extends {
    schema: keyof Database
  }
    ? keyof Database[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"]
    : never = never,
> = PublicCompositeTypeNameOrOptions extends { schema: keyof Database }
  ? Database[PublicCompositeTypeNameOrOptions["schema"]]["CompositeTypes"][CompositeTypeName]
  : PublicCompositeTypeNameOrOptions extends keyof DefaultSchema["CompositeTypes"]
    ? DefaultSchema["CompositeTypes"][PublicCompositeTypeNameOrOptions]
    : never

export const Constants = {
  public: {
    Enums: {},
  },
} as const
