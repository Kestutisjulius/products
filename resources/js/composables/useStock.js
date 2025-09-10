// resources/js/composables/useStock.js
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

export function useStock(productId, intervalMs = 15000) {
  const stock = ref({ total: null, by_city: [] })
  let timer = null

  const fetchStock = async () => {
    const { data } = await axios.get(route('api.products.stock.show', productId))
    stock.value = data
  }

  onMounted(async () => {
    await fetchStock()
    timer = setInterval(fetchStock, intervalMs)
  })
  onUnmounted(() => timer && clearInterval(timer))

  return { stock, refresh: fetchStock }
}
