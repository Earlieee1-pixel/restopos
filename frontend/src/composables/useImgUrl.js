// Shared composable para sa product image URL handling
export function useImgUrl() {
  // I-prefix ang backend URL para sa local storage paths
  const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') ?? 'http://localhost:8000'

  function imgUrl(path) {
    if (!path) return ''
    // Imgur ug uban pa nga external URLs — ibalik as-is
    if (path.startsWith('http')) return path
    return backendUrl + path
  }

  return { imgUrl }
}
