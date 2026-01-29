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
  id?: number
  owner_id: number | null
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
  images?: Array<PropertyImage | File>
  created_at?: string
  owner?: Owner
  images_data?: PropertyImage[]
}

export interface PropertyImage {
  id: number
  property_id: number
  image_url: string
  thumbnail_url: string | null
  sort_order: number
  is_primary: boolean
}

export interface Tag {
  id?: number
  name: string
  color?: string
  description?: string
  is_active?: boolean
}

export interface TagListItem {
  id: number
  name: string
  color: string
  description: string
  properties_count: number
  created_at: string
  is_active?: boolean
}


export interface Owner {
  id: number
  name: string
  email: string
  phone?: string | null
}