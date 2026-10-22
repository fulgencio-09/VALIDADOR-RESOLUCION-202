<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'

const file = ref(null)
const dragging = ref(false)
const loading = ref(false)
const historyLoading = ref(false)
const deleteLoading = ref(false)
const resultsLoading = ref(false)
const correctionLoading = ref(false)
const status = ref('Listo para validar')
const errorMessage = ref('')
const correctionMessage = ref('')
const validation = ref(null)
const history = ref([])
const summary = ref([])
const results = ref([])
const severityFilter = ref('ALL')
const codeFilter = ref('')
const variableFilter = ref('')
const lineFilter = ref('')
const resultsPage = ref(1)
const resultsPerPage = 50
const resultsTotal = ref(0)
const selectedRunId = ref(null)
const pollTimer = ref(null)
const apiUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/$/, '')

const filteredSummary = computed(() => summary.value.filter((item) => !severityFilter.value || severityFilter.value === 'ALL' || item.severity === severityFilter.value))
const totalPages = computed(() => Math.max(1, Math.ceil(resultsTotal.value / resultsPerPage)))
const pageLabel = computed(() => `${resultsTotal.value ? ((resultsPage.value - 1) * resultsPerPage) + 1 : 0}-${Math.min(resultsPage.value * resultsPerPage, resultsTotal.value)} de ${resultsTotal.value}`)

function stopPolling() { if (pollTimer.value) clearInterval(pollTimer.value); pollTimer.value = null }
function clearResults() { results.value = []; summary.value = []; resultsTotal.value = 0; resultsPage.value = 1 }

async function loadResults(id, page = resultsPage.value) {
  resultsLoading.value = true
  try {
    const params = { page, per_page: resultsPerPage }
    if (severityFilter.value !== 'ALL') params.severity = severityFilter.value
    if (codeFilter.value.trim()) params.code = codeFilter.value.trim()
    if (variableFilter.value.trim()) params.variable = variableFilter.value.trim()
    if (lineFilter.value.trim()) params.line = lineFilter.value.trim()
    const { data } = await axios.get(`${apiUrl}/validations/${id}/results`, { params, timeout: 30000 })
    results.value = data.data || data.results || []
    resultsTotal.value = Number(data.total ?? data.meta?.total ?? results.value.length)
    resultsPage.value = Number(data.current_page ?? data.meta?.current_page ?? page)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'No fue posible cargar los resultados paginados.'
  } finally { resultsLoading.value = false }
}

async function loadSummary(id) {
  try { const { data } = await axios.get(`${apiUrl}/validations/${id}/summary`, { timeout: 30000 }); summary.value = data.data || data.summary || [] }
  catch { summary.value = [] }
}

async function refreshResults() { if (validation.value?.id) { await loadSummary(validation.value.id); await loadResults(validation.value.id, 1) } }

async function refreshRun(id) {
  const { data } = await axios.get(`${apiUrl}/validations/${id}`, { timeout: 30000 })
  validation.value = data
  if (data.status === 'COMPLETED') {
    stopPolling(); loading.value = false
    status.value = data.error_count === 0 ? 'Validación finalizada: sin errores.' : `Validación finalizada: ${data.error_count} error(es) y ${data.warning_count} advertencia(s).`
    await Promise.all([loadHistory(), loadSummary(id), loadResults(id, resultsPage.value)])
  } else if (data.status === 'FAILED') { stopPolling(); loading.value = false; status.value = 'La validación terminó con un error.' }
  else status.value = `Validación en segundo plano: ${data.status}...`
  return data
}
function startPolling(id) { stopPolling(); pollTimer.value = setInterval(async () => { try { await refreshRun(id) } catch {} }, 2000) }
async function loadHistory() { historyLoading.value = true; try { const { data } = await axios.get(`${apiUrl}/validations`, { timeout: 15000 }); history.value = data.data || [] } catch { history.value = [] } finally { historyLoading.value = false } }
function selectFile(nextFile) { stopPolling(); file.value = nextFile || null; validation.value = null; selectedRunId.value = null; clearResults(); errorMessage.value = ''; correctionMessage.value = ''; loading.value = false; status.value = file.value ? `Archivo seleccionado: ${file.value.name}` : 'Listo para validar' }
function onInput(event) { selectFile(event.target.files?.[0] ?? null) }
function onDrop(event) { dragging.value = false; selectFile(event.dataTransfer.files?.[0] ?? null) }
async function validateFile() {
  if (!file.value || loading.value) return
  loading.value = true; errorMessage.value = ''; correctionMessage.value = ''; validation.value = null; clearResults(); status.value = 'Cargando archivo y preparando validación...'
  try {
    const form = new FormData(); form.append('file', file.value)
    const { data } = await axios.post(`${apiUrl}/validations`, form, { headers: { 'Content-Type': 'multipart/form-data' }, timeout: 120000 })
    validation.value = data; selectedRunId.value = data.id
    if (data.queued || data.status === 'QUEUED' || data.status === 'PROCESSING') { status.value = 'Archivo grande enviado a procesamiento en segundo plano...'; startPolling(data.id) }
    else { loading.value = false; status.value = data.valid ? 'Validación finalizada: sin errores.' : `Validación finalizada: ${data.error_count} error(es) y ${data.warning_count} advertencia(s).`; await Promise.all([loadHistory(), loadSummary(data.id), loadResults(data.id, 1)]) }
  } catch (error) { errorMessage.value = error.response?.data?.message || 'No fue posible conectar con la API de validación.'; status.value = 'La validación no pudo completarse.'; loading.value = false }
}
async function deleteAllRecords() {
  if (deleteLoading.value) return
  if (!window.confirm('¿Está seguro de eliminar TODOS los registros de validación y los archivos cargados? Esta acción no se puede deshacer.')) return
  deleteLoading.value = true; errorMessage.value = ''; correctionMessage.value = ''
  try {
    const { data } = await axios.delete(`${apiUrl}/validations`, { timeout: 120000 })
    stopPolling()
    file.value = null
    validation.value = null
    selectedRunId.value = null
    clearResults()
    history.value = []
    status.value = data.message || 'Todos los registros fueron eliminados.'
    correctionMessage.value = data.message || 'Todos los registros fueron eliminados.'
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'No fue posible eliminar los registros.'
  } finally { deleteLoading.value = false }
}
async function openRun(id) {
  stopPolling(); selectedRunId.value = id; errorMessage.value = ''; correctionMessage.value = ''; severityFilter.value = 'ALL'; codeFilter.value = ''; variableFilter.value = ''; lineFilter.value = ''; clearResults()
  try { const data = await refreshRun(id); file.value = null; if (data.status === 'QUEUED' || data.status === 'PROCESSING') { loading.value = true; startPolling(id) }; window.scrollTo({ top: 0, behavior: 'smooth' }) }
  catch (error) { errorMessage.value = error.response?.data?.message || 'No fue posible cargar la validación seleccionada.' }
}
async function applyFilters() { if (!validation.value) return; resultsPage.value = 1; await loadResults(validation.value.id, 1) }
async function goPage(page) { if (!validation.value || page < 1 || page > totalPages.value || resultsLoading.value) return; await loadResults(validation.value.id, page) }
function canCorrect(item) { return item.code === 'Error220' && Number.isInteger(Number(item.variable)) && item.line && /^[0-9]+(?:\.[0-9]+)?[eE]\+?[0-9]+$/.test(String(item.value ?? '')) }

function proposedChange(item) {
  const code = String(item.code || '')
  const value = String(item.value ?? '')
  const variable = Number(item.variable)

  if (code === 'Error076' || code === 'Error021') {
    return variable === 90 ? (value === '999' ? '999 → 0 (viceversa)' : value === '0' ? '0 → 999 (viceversa)' : 'Variable 90: 999 ↔ 0') : '—'
  }
  if (code === 'Error082' || code === 'Error621' || code === 'Error622') {
    return variable === 93 ? (value === '1800-01-01' ? '1800-01-01 → 1845-01-01 (viceversa)' : value === '1845-01-01' ? '1845-01-01 → 1800-01-01 (viceversa)' : 'Variable 93: 1800-01-01 ↔ 1845-01-01') : '—'
  }
  if (code === 'Error304' || code === 'Error305') {
    return variable === 53 ? (value === '1800-01-01' ? '1800-01-01 → 1845-01-01 (viceversa)' : value === '1845-01-01' ? '1845-01-01 → 1800-01-01 (viceversa)' : 'Variable 53: 1800-01-01 ↔ 1845-01-01') : '—'
  }
  if (code === 'Error064' || code === 'Error329') {
    return variable === 71 ? (value === '1' ? '1 → 0 (viceversa)' : value === '0' ? '0 → 1 (viceversa)' : value === '21' ? '21 → 1' : 'Variable 71: 1 ↔ 0') : '—'
  }
  if (code === 'Error084') return variable === 94 ? 'Variable 94 → 0' : '—'
  if (code === 'Error223') return 'Cambiar al valor 21 según la regla del error'
  if (code === 'Error227') return 'Cambiar al valor 0 según la regla del error'
  if (code === 'Error308') return variable === 54 ? (value === '21' ? '21 → 0 (viceversa)' : value === '0' ? '0 → 21 (viceversa)' : 'Variable 54: 21 ↔ 0') : '—'
  if (code === 'Error328') return 'Variable 70 → 1; si variable 16 = 4/5, variable 52 toma la fecha de variable 29'
  if (code === 'Error536') return variable === 36 ? (value === '0' ? '0 → 21 (viceversa)' : value === '21' ? '21 → 0 (viceversa)' : 'Variable 36: 0 ↔ 21') : '—'
  if (['Error558', 'Error562', 'Error566', 'Error570'].includes(code)) return variable === 52 ? (value === '1800-01-01' ? '1800-01-01 → 1845-01-01 (viceversa)' : value === '1845-01-01' ? '1845-01-01 → 1800-01-01 (viceversa)' : 'Variable 52: 1800-01-01 ↔ 1845-01-01') : '—'
  if (code === 'Error597') return 'Variable 76 → 1845-01-01 según edad (regla de edad)'
  if (code === 'Error362') return variable === 96 ? (value === '1845-01-01' ? '1845-01-01 → 1800-01-01 (viceversa)' : value === '1800-01-01' ? '1800-01-01 → 1845-01-01 (viceversa)' : 'Variable 96: 1845-01-01 ↔ 1800-01-01') : '—'
  if (code === 'Error669') return variable === 77 ? 'Variable 77 → 0' : '—'
  if (code === 'Error670') return variable === 77 ? 'Variable 77 → 1' : '—'
  if (code === 'Error379') return 'Variables 35, 49, 50, 56, 58, 59, 60 y 61 → valores definidos por la regla'
  if (code === 'Error503') return 'Variable 52 toma la fecha de variable 29 cuando variable 16 = 4 o 5'
  if (code === 'Error524' || code === 'Error525') return 'Ajustar variable relacionada con fecha 29 según la regla'
  if (code === 'Error528' || code === 'Error529') return 'Ajustar variable relacionada con fecha 29 según la regla'
  if (code === 'Error550') return 'Variables 27/28/40/62/63 según fecha 29 y edad'
  if (['Error560', 'Error564', 'Error568', 'Error572'].includes(code)) return 'Variables 43-46 y 52 según fecha 29 y edad'
  if (code === 'Error639') return 'Variables 103/104: 998 ↔ 1800-01-01 y 0 ↔ 1845-01-01 según regla de edad'
  if (code === 'Error634' || code === 'STRUCT011') return 'Omitir error'
  if (code === 'Warning674' || code === 'Warning675') return 'No modificar automáticamente'
  if (code === 'Error635' || code === 'Error678') return 'Ajustar formato del valor según línea (ceros iniciales / 12 ceros)'
  if (code === 'Error150') return 'Variable 96 toma el valor de variable 29'
  return '—'
}

async function correctItem(item) {
  if (!validation.value || !canCorrect(item) || correctionLoading.value) return
  if (!window.confirm(`Convertir el valor de la línea ${item.line}, variable ${item.variable}, de ${item.value} a número decimal exacto?`)) return
  correctionLoading.value = true; errorMessage.value = ''; correctionMessage.value = ''
  try { const { data } = await axios.post(`${apiUrl}/validations/${validation.value.id}/correct`, { corrections: [{ code: item.code, line: Number(item.line), variable: Number(item.variable), action: 'scientific_notation_to_integer' }] }); correctionMessage.value = `Corrección generada: ${data.changes?.length || 0} cambio(s).`; if (data.download_url) window.open(data.download_url, '_blank', 'noopener,noreferrer'); await refreshResults() }
  catch (error) { errorMessage.value = error.response?.data?.message || 'No fue posible generar el archivo corregido.' }
  finally { correctionLoading.value = false }
}
async function correctAutomatically() {
  if (!validation.value || correctionLoading.value || validation.value.error_count === 0) return
  if (!window.confirm('Se aplicarán únicamente las correcciones marcadas como seguras y automáticas por el catálogo. ¿Desea continuar?')) return
  correctionLoading.value = true; errorMessage.value = ''; correctionMessage.value = ''
  try {
    const { data } = await axios.post(`${apiUrl}/validations/${validation.value.id}/auto-correct`, {}, { timeout: 120000 })
    const verification = data.verification || {}
    correctionMessage.value = data.changes?.length
      ? `Corrección automática completada: ${data.changes.length} cambio(s). Revalidación: ${verification.error_count ?? 0} error(es) y ${verification.warning_count ?? 0} advertencia(s).`
      : 'No se encontraron correcciones automáticas seguras para esta validación.'

    // El backend revalida y guarda los resultados corregidos. Recargamos
    // inmediatamente la validación, el resumen y la tabla para no mostrar
    // los errores anteriores después de aplicar la corrección.
    await refreshRun(validation.value.id)

    if (data.download_url) window.open(data.download_url, '_blank', 'noopener,noreferrer')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'No fue posible ejecutar las correcciones automáticas.'
  } finally { correctionLoading.value = false }
}
function formatBytes(bytes) { if (!bytes) return '0 B'; const units = ['B', 'KB', 'MB', 'GB']; const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1); return `${(bytes / 1024 ** index).toFixed(index ? 1 : 0)} ${units[index]}` }
function formatDate(value) { if (!value) return '—'; return new Date(value).toLocaleString('es-CO', { dateStyle: 'short', timeStyle: 'short' }) }
function exportReport() { if (!validation.value) return; window.open(`${apiUrl}/validations/${validation.value.id}/report`, '_blank', 'noopener,noreferrer') }
onMounted(loadHistory)
onUnmounted(stopPolling)
</script>

<template>
  <main class="app-shell">
    <header class="topbar"><div><span class="badge">RESOLUCIÓN 202 DE 2021</span><h1>Validador RPED</h1><p class="hero-copy">Cargue un TXT, ejecute la validación estructural y las reglas parametrizadas y revise los hallazgos por línea.</p></div><span class="api-pill">API V1</span></header>
    <section class="card upload-card"><div class="section-heading"><div><h2>Validar archivo</h2><p class="muted">Anexo Técnico 1 · Registro por persona (RPED)</p></div><span v-if="file" class="file-size">{{ formatBytes(file.size) }}</span></div><label class="dropzone" :class="{ dragging }" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="onDrop"><input type="file" accept=".txt,text/plain" @change="onInput" /><span class="upload-icon">↑</span><strong>{{ file ? file.name : 'Seleccione o arrastre un archivo TXT' }}</strong><span>Máximo 50 MB · contenido delimitado por |</span></label><div class="status" :class="{ success: validation?.status === 'COMPLETED' && validation?.error_count === 0, danger: errorMessage }">{{ status }}</div><div v-if="validation && (validation.status === 'QUEUED' || validation.status === 'PROCESSING')" class="progress-box"><div class="progress-bar"><span :style="{ width: `${validation.progress || 0}%` }"></span></div><small>Procesamiento en segundo plano · {{ validation.progress || 0 }}%</small></div><div v-if="errorMessage" class="alert danger-box">{{ errorMessage }}</div><div v-if="correctionMessage" class="alert success-box">{{ correctionMessage }}</div><div class="actions"><button class="primary" :disabled="!file || loading" @click="validateFile">{{ loading ? 'Procesando...' : 'Iniciar validación' }}</button><button v-if="validation?.status === 'COMPLETED'" class="secondary" @click="exportReport">Descargar reporte CSV</button><button v-if="validation?.status === 'COMPLETED' && validation?.error_count > 0" class="auto-correct-btn" :disabled="correctionLoading" @click="correctAutomatically">{{ correctionLoading ? 'Corrigiendo...' : '🔧 Corregir automáticamente' }}</button></div></section>
    <section v-if="validation?.status === 'COMPLETED'" class="metrics"><article class="card metric"><span>Registros</span><strong>{{ validation.records }}</strong></article><article class="card metric"><span>Errores</span><strong class="danger-text">{{ validation.error_count }}</strong></article><article class="card metric"><span>Advertencias</span><strong>{{ validation.warning_count }}</strong></article><article class="card metric"><span>Reglas ejecutadas</span><strong>{{ validation.rules_loaded }}</strong></article></section>
    <section class="card history-card"><div class="section-heading"><div><h2>Historial de validaciones</h2><p class="muted">Últimas 50 validaciones procesadas.</p></div><div class="actions"><button class="secondary small" :disabled="historyLoading" @click="loadHistory">{{ historyLoading ? 'Actualizando...' : 'Actualizar' }}</button><button class="secondary small" :disabled="deleteLoading" @click="deleteAllRecords">{{ deleteLoading ? 'Eliminando...' : 'Eliminar registros' }}</button></div></div><div v-if="history.length" class="history-list"><button v-for="run in history" :key="run.id" class="history-item" :class="{ selected: selectedRunId === run.id }" @click="openRun(run.id)"><span class="history-main"><strong>{{ run.filename }}</strong><small>{{ formatDate(run.validated_at || run.created_at) }} · {{ formatBytes(run.size_bytes) }}</small></span><span class="history-stats"><b>{{ run.records }}</b> registros <span class="status-mini" :class="run.status.toLowerCase()">{{ run.status }}</span><span class="danger-text">{{ run.error_count }} errores</span><span>{{ run.warning_count }} advertencias</span></span></button></div><div v-else class="empty-state">Todavía no hay validaciones guardadas.</div></section>
    <section v-if="validation?.status === 'COMPLETED'" class="card results-card">
      <div class="section-heading results-heading"><div><h2>Resultados profesionales</h2><p class="muted">{{ pageLabel }} hallazgos · página {{ resultsPage }} de {{ totalPages }}.</p></div><span class="valid-state" :class="validation.error_count === 0 ? 'ok' : 'bad'">{{ validation.error_count === 0 ? 'SIN ERRORES' : 'REQUIERE CORRECCIÓN' }}</span></div>
      <div v-if="filteredSummary.length" class="summary-grid"><article v-for="item in filteredSummary" :key="`${item.code}-${item.severity}`" class="summary-item"><strong>{{ item.code }}</strong><span>{{ item.count }} caso(s)</span><small>{{ item.severity }}</small></article></div>
      <div class="filters"><select v-model="severityFilter"><option value="ALL">Todas las severidades</option><option value="ERROR">Errores</option><option value="WARNING">Advertencias</option></select><input v-model="codeFilter" type="search" placeholder="Código, ej. Error305" @keyup.enter="applyFilters" /><input v-model="variableFilter" type="search" placeholder="Variable" @keyup.enter="applyFilters" /><input v-model="lineFilter" type="search" placeholder="Línea" @keyup.enter="applyFilters" /><button class="secondary small" :disabled="resultsLoading" @click="applyFilters">{{ resultsLoading ? 'Consultando...' : 'Aplicar filtros' }}</button></div>
      <div v-if="resultsLoading" class="empty-state">Cargando resultados...</div>
      <div v-else-if="results.length" class="table-wrap">
        <table>
          <thead><tr><th>Código</th><th>Severidad</th><th>Línea</th><th>Variable</th><th>Edad</th><th>Mensaje</th><th>Valor actual</th><th>Cambio propuesto</th><th>Acción</th></tr></thead>
          <tbody>
            <tr v-for="(item, index) in results" :key="`${item.code}-${item.line}-${index}`">
              <td><code>{{ item.code }}</code></td>
              <td><span class="severity" :class="item.severity.toLowerCase()">{{ item.severity }}</span></td>
              <td>{{ item.line ?? '—' }}</td>
              <td>{{ item.variable ?? '—' }}</td>
              <td>{{ item.age !== null && item.age !== undefined ? `${item.age} años` : '—' }}</td>
              <td>{{ item.message }}</td>
              <td class="value-cell">{{ item.value ?? '—' }}</td>
              <td class="change-cell"><strong v-if="proposedChange(item) !== '—'">{{ proposedChange(item) }}</strong><span v-else class="muted">Sin corrección parametrizada</span></td>
              <td><button v-if="canCorrect(item)" class="correct-btn" :disabled="correctionLoading" @click="correctItem(item)">{{ correctionLoading ? 'Generando...' : 'Corregir' }}</button><span v-else class="muted">Manual</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else class="empty-state">No hay hallazgos con los filtros seleccionados.</div>
      <div v-if="resultsTotal" class="pagination"><button class="secondary small" :disabled="resultsPage <= 1 || resultsLoading" @click="goPage(resultsPage - 1)">Anterior</button><span>Página {{ resultsPage }} de {{ totalPages }}</span><button class="secondary small" :disabled="resultsPage >= totalPages || resultsLoading" @click="goPage(resultsPage + 1)">Siguiente</button></div>
    </section>
  </main>
</template>