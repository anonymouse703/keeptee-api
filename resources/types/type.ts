// Base tag interface for forms
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