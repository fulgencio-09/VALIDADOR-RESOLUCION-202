<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

const file = ref(null)
const dragging = ref(false)
const loading = ref(false)
const historyLoading = ref(false)
const status = ref('Listo para validar')
const errorMessage = ref('')
const validation = ref(null)
const history = ref([])
const severityFilter = ref('ALL')
const codeFilter = ref('')
const selectedRunId = ref(null)
const apiUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/$/, '')

const filteredResults = computed(() => {
  if (!validation.value) return []
  const code = codeFilter.value.trim().toLowerCase()
  return validation.value.results
    .filter((item) => severityFilter.value === 'ALL' || item.severity === severityFilter.value)
    .filter((item) => !code || item.code.toLowerCase().includes(code))
})

async function loadHistory() {
  historyLoading.value = true
  try {
    const { data } = await axios.get(`${apiUrl}/validations`, { timeout: 15000 })
    history.value = data.data || []
  } catch {
    history.value = []
  } finally {
    historyLoading.value = false
  }
}

function selectFile(nextFile) {
  file.value = nextFile || null
  validation.value = null
  selectedRunId.value = null
  errorMessage.value = ''
  status.value = file.value ? `Archivo seleccionado: ${file.value.name}` : 'Listo para validar'
}

function onInput(event) {
  selectFile(event.target.files?.[0] ?? null)
}

function onDrop(event) {
  dragging.value = false
  selectFile(event.dataTransfer.files?.[0] ?? null)
}

async function validateFile() {
  if (!file.value || loading.value) return

  loading.value = true
  errorMessage.value = ''
  status.value = 'Procesando archivo y ejecutando reglas...'
  validation.value = null

  try {
    const form = new FormData()
    form.append('file', file.value)
    const { data } = await axios.post(`${apiUrl}/validations`, form, {
      headers: { 'Content-Type': 'multipart/form-data' },
      timeout: 120000,
    })
    validation.value = data
    selectedRunId.value = data.id
    status.value = data.valid
      ? 'Validación finalizada: sin errores.'
      : `Validación finalizada: ${data.error_count} error(es) y ${data.warning_count} advertencia(s).`
    await loadHistory()
  } catch (error) {
    const response = error.response?.data
    errorMessage.value = response?.message || 'No fue posible conectar con la API de validación.'
    status.value = 'La validación no pudo completarse.'
  } finally {
    loading.value = false
  }
}

async function openRun(id) {
  selectedRunId.value = id
  errorMessage.value = ''
  try {
    const { data } = await axios.get(`${apiUrl}/validations/${id}`, { timeout: 30000 })
    validation.value = data
    file.value = null
    status.value = `Historial cargado: ${data.filename}`
    severityFilter.value = 'ALL'
    codeFilter.value = ''
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'No fue posible cargar la validación seleccionada.'
  }
}

function formatBytes(bytes) {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1)
  return `${(bytes / 1024 ** index).toFixed(index ? 1 : 0)} ${units[index]}`
}

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' })
}

function exportReport() {
  if (!validation.value) return
  const rows = [
    ['Código', 'Severidad', 'Línea', 'Variable', 'Mensaje', 'Valor'],
    ...filteredResults.value.map((item) => [item.code, item.severity, item.line ?? '', item.variable ?? '', item.message, item.value ?? '']),
  ]
  const csv = rows.map((row) => row.map((value) => `"${String(value).replaceAll('"', '""')}"`).join(';')).join('\r\n')
  const blob = new Blob([`\uFEFF${csv}`], { type: 'text/csv;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `reporte-res202-${validation.value.filename || 'validacion'}.csv`
  link.click()
  URL.revokeObjectURL(url)
}

onMounted(loadHistory)
</script>

<template>
  <main class="app-shell">
    <header class="topbar">
      <div>
        <span class="badge">RESOLUCIÓN 202 DE 2021</span>
        <h1>Validador RPED</h1>
        <p class="hero-copy">Cargue un TXT, ejecute la validación estructural y las reglas parametrizadas y revise los hallazgos por línea.</p>
      </div>
      <span class="api-pill">API V1</span>
    </header>

    <section class="card upload-card">
      <div class="section-heading">
        <div>
          <h2>Validar archivo</h2>
          <p class="muted">Anexo Técnico 1 · Registro por persona (RPED)</p>
        </div>
        <span v-if="file" class="file-size">{{ formatBytes(file.size) }}</span>
      </div>

      <label
        class="dropzone"
        :class="{ dragging }"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop.prevent="onDrop"
      >
        <input type="file" accept=".txt,text/plain" @change="onInput" />
        <span class="upload-icon">↑</span>
        <strong>{{ file ? file.name : 'Seleccione o arrastre un archivo TXT' }}</strong>
        <span>Máximo 50 MB · contenido delimitado por |</span>
      </label>

      <div class="status" :class="{ success: validation?.valid, danger: errorMessage }">{{ status }}</div>
      <div v-if="errorMessage" class="alert danger-box">{{ errorMessage }}</div>

      <div class="actions">
        <button class="primary" :disabled="!file || loading" @click="validateFile">
          {{ loading ? 'Validando...' : 'Iniciar validación' }}
        </button>
        <button v-if="validation" class="secondary" @click="exportReport">Exportar reporte CSV</button>
      </div>
    </section>

    <section v-if="validation" class="metrics">
      <article class="card metric"><span>Registros</span><strong>{{ validation.records }}</strong></article>
      <article class="card metric"><span>Errores</span><strong class="danger-text">{{ validation.error_count }}</strong></article>
      <article class="card metric"><span>Advertencias</span><strong>{{ validation.warning_count }}</strong></article>
      <article class="card metric"><span>Reglas ejecutadas</span><strong>{{ validation.rules_loaded }}</strong></article>
    </section>

    <section class="card history-card">
      <div class="section-heading">
        <div>
          <h2>Historial de validaciones</h2>
          <p class="muted">Últimas 50 validaciones procesadas.</p>
        </div>
        <button class="secondary small" :disabled="historyLoading" @click="loadHistory">{{ historyLoading ? 'Actualizando...' : 'Actualizar' }}</button>
      </div>

      <div v-if="history.length" class="history-list">
        <button
          v-for="run in history"
          :key="run.id"
          class="history-item"
          :class="{ selected: selectedRunId === run.id }"
          @click="openRun(run.id)"
        >
          <span class="history-main">
            <strong>{{ run.filename }}</strong>
            <small>{{ formatDate(run.validated_at) }} · {{ formatBytes(run.size_bytes) }}</small>
          </span>
          <span class="history-stats">
            <b>{{ run.records }}</b> registros
            <span class="danger-text">{{ run.error_count }} errores</span>
            <span>{{ run.warning_count }} advertencias</span>
          </span>
        </button>
      </div>
      <div v-else class="empty-state">Todavía no hay validaciones guardadas.</div>
    </section>

    <section v-if="validation" class="card results-card">
      <div class="section-heading results-heading">
        <div>
          <h2>Resultados</h2>
          <p class="muted">{{ filteredResults.length }} hallazgo(s) visibles de {{ validation.result_count }}.</p>
        </div>
        <span class="valid-state" :class="validation.error_count === 0 ? 'ok' : 'bad'">{{ validation.error_count === 0 ? 'SIN ERRORES' : 'REQUIERE CORRECCIÓN' }}</span>
      </div>

      <div class="filters">
        <select v-model="severityFilter">
          <option value="ALL">Todas las severidades</option>
          <option value="ERROR">Errores</option>
          <option value="WARNING">Advertencias</option>
        </select>
        <input v-model="codeFilter" type="search" placeholder="Filtrar por código, ej. Error305" />
      </div>

      <div v-if="filteredResults.length" class="table-wrap">
        <table>
          <thead><tr><th>Código</th><th>Severidad</th><th>Línea</th><th>Variable</th><th>Mensaje</th><th>Valor</th></tr></thead>
          <tbody>
            <tr v-for="(item, index) in filteredResults" :key="`${item.code}-${item.line}-${index}`">
              <td><code>{{ item.code }}</code></td>
              <td><span class="severity" :class="item.severity.toLowerCase()">{{ item.severity }}</span></td>
              <td>{{ item.line ?? '—' }}</td>
              <td>{{ item.variable ?? '—' }}</td>
              <td>{{ item.message }}</td>
              <td class="value-cell">{{ item.value ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">No hay hallazgos con los filtros seleccionados.</div>
    </section>
  </main>
</template>
