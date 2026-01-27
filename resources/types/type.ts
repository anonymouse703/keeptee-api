export interface PaginationMeta {
    current_page: number
    last_page: number
    links: {
        url: string | null
        label: string
        active: boolean
    }[]
    total?: number
    from?: number
    to?: number
}

// types/property.ts
export interface PropertyListItem {
  id: number
  title: string
  status: 'available' | 'sold' | 'rented' | string
  property_type: string
  price: number | string
  is_featured?: boolean
  is_active?: boolean
  created_at: string
  properties_count: number
}

export interface Property {
  title: string
  description: string
  status: string
  property_type: string
  price?: number | null
  bedrooms?: number | null
  bathrooms?: number | null
  floor_area?: number | null
  address: string
  city: string
  state: string
  country: string
  latitude?: number | null
  longitude?: number | null
  is_featured?: boolean
  is_active?: boolean
}


export interface Tag {
  id?: number
  name: string
  color?: string
  description?: string
  is_active?: boolean
}

// Tag with required ID for editing
export interface TagWithId extends Tag {
  id: number
}

// Tag list item for index/listing views
export interface TagListItem {
  id: number
  name: string
  color: string
  description: string
  properties_count: number
  created_at: string
  is_active?: boolean
}


